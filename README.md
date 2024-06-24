<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## <p style="text-align: center;">Twitch API</p>

This project is a **Twitch API** developed in `PHP` using the `Laravel` framework and `Docker` for containerization. It adheres to Clean Code principles, includes comprehensive testing, and incorporates regular code refactoring to maintain high code quality. The API provides the following endpoints: 

> ### **/streamers:id** (`GET`): Retrieves data for a given Twitch streamer by id.
>>```bash 
>>  curl -X GET 'http://localhost/analytics/streamers?id=459331509'
>>```
> ### **/streams** (`GET`): Obtains live Twitch streams.
>>```bash 
>>  curl -X GET 'http://localhost/analytics/streams'
>>```
> ### **/topsofthetops:since** (`GET`): Retrieves data for the top 40 videos of each of the top three most popular games, with an optional integer parameter `since` to filter results based on time (seconds).
>>```bash 
>>  curl -X GET 'http://localhost/analytics/topsofthetops'
>>```
>>```bash 
>>  curl -X GET 'http://localhost/analytics/topsofthetops?since=150'
>>```
> ### **/timeline:username** (`GET`): Retrieves the list of recent streams from followed streamers.
>>```bash 
>>  curl -X GET 'http://localhost/analytics/timeline?username=user_name'
>>```
> ### **/follow** (`POST`): Allows registered users to follow a streamer.
>>```bash 
>>  curl -X POST 'http://localhost/analytics/follow' \
>>  -H "Content-Type: application/json" \
>>  -d '{"username": "user_name", "streamerId": "streamer_id"}'
>>```
> ### **/unfollow** (`DELETE`): Allows registered users to unfollow a streamer.
>>```bash 
>>  curl -X DELETE 'http://localhost/analytics/unfollow' \
>>  -H "Content-Type: application/json" \
>>  -d '{"username": "user_name", "streamerId": "streamer_id"}'
>>```
> ### **/users** (`POST`): Creates a new user with a specified username.
>>```bash 
>>  curl -X POST 'http://localhost/analytics/users' \
>>  -H "Content-Type: application/json" \
>>  -d '{"username": "user_name", "password": "password"}'
>>```
> ### **/users** (`GET`): Retrieves a list of all registered users, including the streamers each user follows.
>>```bash 
>>  curl -X GET 'http://localhost/analytics/users'
>>```
---
### How to run the project?
#### 1. Clone Repository:
```bash
  git clone git@github.com:sayechu/laravel-twitch-api.git
```
#### 2. Move to directory and composer install:
```bash
  cd laravel-twitch-api
  composer install
```
#### 3. Copy environment variables file:
```bash
  cp .env.example .env
```
#### 4. Run sail
Note I: `alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'` \
Note II: run Docker Desktop before executing this command
```bash
  sail up -d
```
#### 5. Run migrations

```bash
  sail php artisan migrate
```
---
- To **stop the running containers**, execute:
```bash 
  sail down
```
- To run the **unitary and integrations tests**:
```bash
  ./vendor/bin/phpunit
```
- To rollback the migrations:
```bash
  sail php artisan migrate:rollback
```
