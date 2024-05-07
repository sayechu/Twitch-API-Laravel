<?php

namespace App\Services;

class ApiClient
{
    private const CLIENT_ID = '970almy6xw98ruyojcwqpop0p0o5a2';
    private const CLIENT_SECRET = 'yl0nqzjjnadd8wl7zilpr9pzuh979j';
    private const GRANT_TYPE = 'client_credentials';

    public function getToken(): array
    {
        $url = 'https://id.twitch.tv/oauth2/token';
        $data = array(
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'grant_type' => self::GRANT_TYPE
        );

        $curlHeaders = curl_init();
        curl_setopt($curlHeaders, CURLOPT_URL, $url);
        curl_setopt($curlHeaders, CURLOPT_POST, 1);
        curl_setopt($curlHeaders, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curlHeaders, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHeaders, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $response = curl_exec($curlHeaders);
        $http_code = curl_getinfo($curlHeaders, CURLINFO_HTTP_CODE);

        curl_close($curlHeaders);

        return [
            'response' => $response,
            'http_code' => $http_code
        ];
    }

    public function makeCurlCall(string $api_url, array $api_headers): array
    {
        $api_headers[] = 'Client-Id: ' . self::CLIENT_ID;

        $curlHeaders = curl_init();
        curl_setopt($curlHeaders, CURLOPT_URL, $api_url);
        curl_setopt($curlHeaders, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHeaders, CURLOPT_HTTPHEADER, $api_headers);

        $api_response = curl_exec($curlHeaders);
        $http_code = curl_getinfo($curlHeaders, CURLINFO_HTTP_CODE);

        curl_close($curlHeaders);

        return [
            'response' => $api_response,
            'http_code' => $http_code
        ];
    }

    public function getTopThreeGames(array $api_headers): array
    {
        $api_url = "https://api.twitch.tv/helix/games/top?first=3";

        $topGamesData = $this->makeCurlCall($api_url, $api_headers);

        return json_decode($topGamesData, true)['data'];
    }

    public function getTopFourtyVideos($gameId): array
    {
        $api_url = "https://api.twitch.tv/helix/videos?game_id={$gameId}&sort=views&first=40";

        $responseGetToken = $this->getToken();
        $twitchToken = json_decode($responseGetToken, true)['access_token'];

        $api_headers = array(
            'Authorization: Bearer ' . $twitchToken,
        );

        $topVideosData = $this->makeCurlCall($api_url, $api_headers);

        return json_decode($topVideosData, true)['data'];
    }
}
