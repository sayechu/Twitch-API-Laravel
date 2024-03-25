<?php

namespace App\Services;

require_once __DIR__ . '/Database.php';

class TwitchApi
{
    private $client_id;
    private $client_secret;
    private $grant_type;
    private $token;
    private $db;

    public function __construct($client_id, $client_secret, $grant_type = 'client_credentials')
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->grant_type = $grant_type;
        $this->db = new \App\Services\Database();
        $this->token = $this->obtenerToken();
    }

    private function obtenerToken()
    {
        if ($this->db->existeTokenDB()) {
            $getToken = $this->db->getTokenDB();
        } else {
            $getToken = $this->peticionTokenTwitch();
        }
        return $getToken;
    }

    private function peticionTokenTwitch()
    {
        $url = 'https://id.twitch.tv/oauth2/token';
        $data = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => $this->grant_type
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error en la petición cURL para obtener el token: ' . curl_error($ch);
            exit;
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['access_token'])) {
            $this->token = $result['access_token'];
            $db = new \App\Services\Database();
            $db->insertarToken($this->token);
        } else {
            echo 'Error al obtener el token.';
            exit;
        }
        return $this->token;
    }

    public function getRespuestaCurl($api_url)
    {
        $api_headers = array(
            'Authorization: Bearer ' . $this->token,
            'Client-Id: ' . $this->client_id
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $api_headers);
        $api_response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if (curl_errno($ch)) {
            echo 'Error in cURL request to get live streams: ' . curl_error($ch);
            exit;
        }
        curl_close($ch);
        if ($http_status == 401) {
            echo "Token expirado asi que hay que pedirlo de nuevo\n";
            $this->db->exec("TRUNCATE TABLE TOKEN CASCADE;");
            $this->peticionTokenTwitch();
            return $this->getRespuestaCurl($api_url);
        }
        return $api_response;
    }

    public function getInfoUser($userId)
    {
        if ($this->db->comprobarIdUsuarioEnDB($userId)) {
            // DEVOLVER lo de la DB
            $api_response_array = ['data' => [$this->db->devolverUsuarioDeBD($userId)]];
        } else {
            // PETICIÓN a TWITCH API
            $userId = urlencode($userId);
            $api_url = "https://api.twitch.tv/helix/users?id=$userId";
            $api_response = $this->getRespuestaCurl($api_url);
            $api_response_array = json_decode($api_response, true);
            $this->db->anadirUsuarioAdB($api_response_array['data'][0]);
        }
        $api_response_pretty = json_encode($api_response_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->mostrarRespuestaJson($api_response_pretty);
    }

    public function getStreams()
    {
        $api_url = 'https://api.twitch.tv/helix/streams';
        $api_response = $this->getRespuestaCurl($api_url);

        $streams = json_decode($api_response, true);

        if ($streams === null) {
            echo 'Error decoding JSON response from Twitch API.';
            exit;
        }

        if (isset($streams['data']) && is_array($streams['data'])) {
            $filtered_streams = [];

            foreach ($streams['data'] as $stream) {
                $filtered_streams[] = [
                    'title' => $stream['title'],
                    'user_name' => $stream['user_name']
                ];
            }

            $api_response_pretty = json_encode($filtered_streams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->mostrarRespuestaJson($api_response_pretty);
        } else {
            echo '\nNo live streams data found in the Twitch API response.';
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
