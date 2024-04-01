<?php

namespace App\Services;

use App\Services\Database;

class TwitchApi
{
    private $client_id;
    private $client_secret;
    private $grant_type;
    private $token;
    private $dbInstance;

    public function __construct($client_id, $client_secret, $grant_type = 'client_credentials')
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->grant_type = $grant_type;
        $this->dbInstance = new Database();
        $this->token = $this->obtenerToken();
    }

    private function obtenerToken()
    {
        if ($this->dbInstance->existeTokenDB()) {
            return $this->dbInstance->getTokenDB();
        }
        return $this->peticionTokenTwitch();
    }

    private function peticionTokenTwitch()
    {
        $url = 'https://id.twitch.tv/oauth2/token';
        $data = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => $this->grant_type
        );

        $curlHeaders = curl_init();
        curl_setopt($curlHeaders, CURLOPT_URL, $url);
        curl_setopt($curlHeaders, CURLOPT_POST, 1);
        curl_setopt($curlHeaders, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curlHeaders, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHeaders, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $response = curl_exec($curlHeaders);

        if (curl_errno($curlHeaders)) {
            echo 'Error en la petición cURL para obtener el token: ' . curl_error($curlHeaders);
            exit;
        }

        curl_close($curlHeaders);

        $result = json_decode($response, true);

        if (isset($result['access_token'])) {
            $this->token = $result['access_token'];
            $dbInstance = new Database();
            $dbInstance->insertarToken($this->token);
        }

        return $this->token;
    }

    public function getRespuestaCurl($api_url)
    {
        $api_headers = array(
            'Authorization: Bearer ' . $this->token,
            'Client-Id: ' . $this->client_id
        );

        $curlHeaders = curl_init();
        curl_setopt($curlHeaders, CURLOPT_URL, $api_url);
        curl_setopt($curlHeaders, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHeaders, CURLOPT_HTTPHEADER, $api_headers);
        $api_response = curl_exec($curlHeaders);
        $http_status = curl_getinfo($curlHeaders, CURLINFO_HTTP_CODE);


        if (curl_errno($curlHeaders)) {
            echo 'Error in cURL request to get live streams: ' . curl_error($curlHeaders);
            exit;
        }
        curl_close($curlHeaders);
        if ($http_status == 401) {
            echo "Token expirado asi que hay que pedirlo de nuevo\n";
            $this->dbInstance->exec("TRUNCATE TABLE TOKEN CASCADE;");
            $this->peticionTokenTwitch();
            return $this->getRespuestaCurl($api_url);
        }
        return $api_response;
    }

    public function getInfoUser($userId)
    {
        if ($this->dbInstance->comprobarIdUsuarioEnDB($userId)) {
            return ['data' => [$this->dbInstance->devolverUsuarioDeBD($userId)]];
        }

        $userId = urlencode($userId);
        $api_url = "https://api.twitch.tv/helix/users?id=$userId";
        $api_response = $this->getRespuestaCurl($api_url);
        $api_response_array = json_decode($api_response, true);
        $this->dbInstance->anadirUsuarioAdB($api_response_array['data'][0]);

        return $api_response_array;
    }

    public function getStreams()
    {
        $api_url = 'https://api.twitch.tv/helix/streams';
        $api_response = $this->getRespuestaCurl($api_url);

        $streams = json_decode($api_response, true);

        if ($streams === null) {
            return ['error' => 'Error decoding JSON response from Twitch API.'];
        }

        if (isset($streams['data']) && is_array($streams['data'])) {
            $filtered_streams = [];

            foreach ($streams['data'] as $stream) {
                $filtered_streams[] = [
                    'title' => $stream['title'],
                    'user_name' => $stream['user_name']
                ];
            }

            return $filtered_streams;
        }
    }

    public function getTopGames()
    {
        $topGamesUrl = "https://api.twitch.tv/helix/games/top?first=3";
        $response = $this->getRespuestaCurl($topGamesUrl);

        $topGamesData = json_decode($response, true);

        if ($topGamesData === null) {
            echo 'Error al decodificar la respuesta JSON para obtener los juegos más populares.';
            exit;
        }

        return $topGamesData;
    }

    public function getTop40VideosDadoUnGameId($gameId)
    {
        $top40Videos = "https://api.twitch.tv/helix/videos?game_id={$gameId}&sort=views&first=40";
        $response = $this->getRespuestaCurl($top40Videos);

        $topVideosData = json_decode($response, true);

        if ($topVideosData === null || !isset($topVideosData['data'])) {
            echo 'Error al decodificar la respuesta JSON para el juego con ID ' . $gameId;
        }

        return $topVideosData;
    }

    public function mostrarRespuestaJson($api_response_pretty)
    {
        header('Content-Type: application/json');
        echo $api_response_pretty;
    }
}
