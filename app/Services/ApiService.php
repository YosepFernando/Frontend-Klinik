<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ApiService
{
    protected $client;
    protected $baseUrl;
    
    public function __construct()
    {
        // Ambil base URL dari .env, fallback ke default jika tidak ada
        $this->baseUrl = env('API_BASE_URL', 'http://127.0.0.1:8002/api');
        
        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',  // Pastikan ada trailing slash
            'timeout' => 30,
            'verify' => false,  // Untuk development, disable SSL verification
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    /**
     * Set the API token for authenticated requests
     *
     * @param string $token
     * @return $this
     */
    public function withToken($token = null)
    {
        $token = $token ?: Session::get('api_token');
        
        \Log::info('Using API token', ['token_length' => $token ? strlen($token) : 0]);
        
        if ($token) {
            $this->client = new Client([
                'base_uri' => rtrim($this->baseUrl, '/') . '/',  // Konsisten dengan constructor
                'timeout' => 30,
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
        } else {
            \Log::warning('API token not found in session');
        }
        
        return $this;
    }
    
    /**
     * Execute GET request
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     */
    public function get($endpoint, $query = [])
    {
        try {
            // Log URL yang akan dipanggil
            $fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
            Log::info('Making GET request to: ' . $fullUrl, ['query' => $query]);
            
            $response = $this->client->get(ltrim($endpoint, '/'), [
                'query' => $query,
            ]);
            
            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true);
            
            // Periksa apakah JSON decode berhasil
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResponse)) {
                return $decodedResponse;
            } else {
                // Jika JSON tidak valid, return error
                Log::error('Invalid JSON response from API', [
                    'endpoint' => $endpoint,
                    'response_body' => $responseBody,
                    'json_error' => json_last_error_msg()
                ]);
                
                return [
                    'status' => 'error',
                    'message' => 'Respons server tidak valid',
                ];
            }
        } catch (\Exception $e) {
            Log::error('API GET Error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'query' => $query,
                'error' => $e->getMessage(),
                'full_url' => $this->baseUrl . '/' . $endpoint,
            ]);
            
            // Periksa apakah exception memiliki response (untuk HTTP errors)
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorResponse = json_decode($errorBody, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($errorResponse)) {
                    return $errorResponse;
                }
            }
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Execute POST request
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    public function post($endpoint, $data = [])
    {
        try {
            // Log URL yang akan dipanggil
            $fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
            Log::info('Making POST request to: ' . $fullUrl, ['data' => $data]);
            
            $response = $this->client->post(ltrim($endpoint, '/'), [
                'json' => $data,
            ]);
            
            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true);
            
            // Periksa apakah JSON decode berhasil
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResponse)) {
                return $decodedResponse;
            } else {
                // Jika JSON tidak valid, return error
                Log::error('Invalid JSON response from API', [
                    'endpoint' => $endpoint,
                    'response_body' => $responseBody,
                    'json_error' => json_last_error_msg()
                ]);
                
                return [
                    'status' => 'error',
                    'message' => 'Respons server tidak valid',
                ];
            }
        } catch (\Exception $e) {
            Log::error('API POST Error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            
            // Periksa apakah exception memiliki response (untuk HTTP errors)
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorResponse = json_decode($errorBody, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($errorResponse)) {
                    return $errorResponse;
                }
            }
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Execute PUT request
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    public function put($endpoint, $data = [])
    {
        try {
            // Log URL yang akan dipanggil
            $fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
            Log::info('Making PUT request to: ' . $fullUrl, ['data' => $data]);
            
            $response = $this->client->put(ltrim($endpoint, '/'), [
                'json' => $data,
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('API PUT Error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            
            // Periksa apakah exception memiliki response (untuk HTTP errors)
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorResponse = json_decode($errorBody, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($errorResponse)) {
                    return $errorResponse;
                }
            }
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Execute DELETE request
     *
     * @param string $endpoint
     * @return array
     */
    public function delete($endpoint)
    {
        try {
            // Log URL yang akan dipanggil
            $fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
            Log::info('Making DELETE request to: ' . $fullUrl);
            
            $response = $this->client->delete(ltrim($endpoint, '/'));
            
            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true);
            
            // Periksa apakah JSON decode berhasil
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResponse)) {
                return $decodedResponse;
            } else {
                // Jika JSON tidak valid, return error
                Log::error('Invalid JSON response from API DELETE', [
                    'endpoint' => $endpoint,
                    'response_body' => $responseBody,
                    'json_error' => json_last_error_msg()
                ]);
                
                return [
                    'status' => 'error',
                    'message' => 'Respons server tidak valid',
                ];
            }
        } catch (\Exception $e) {
            Log::error('API DELETE Error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            
            // Periksa apakah exception memiliki response (untuk HTTP errors)
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorResponse = json_decode($errorBody, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($errorResponse)) {
                    return $errorResponse;
                }
            }
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Test koneksi ke API server
     *
     * @return bool
     */
    public function testConnection()
    {
        try {
            $response = $this->get('health');
            return isset($response['status']) && $response['status'] === 'success';
        } catch (\Exception $e) {
            Log::error('API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Upload file dengan multipart
     *
     * @param string $endpoint
     * @param array $data
     * @param array $files
     * @return array
     */
    public function uploadFile($endpoint, $data = [], $files = [])
    {
        try {
            $multipart = [];
            
            // Add regular form data
            foreach ($data as $key => $value) {
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
            
            // Add files
            foreach ($files as $key => $file) {
                if ($file) {
                    $multipart[] = [
                        'name' => $key,
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ];
                }
            }
            
            // Create client without Content-Type header for multipart
            $uploadClient = new Client([
                'base_uri' => rtrim($this->baseUrl, '/') . '/',
                'timeout' => 60, // Longer timeout for uploads
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => Session::get('api_token') ? 'Bearer ' . Session::get('api_token') : '',
                ],
            ]);
            
            $response = $uploadClient->post(ltrim($endpoint, '/'), [
                'multipart' => $multipart,
            ]);
            
            $responseBody = $response->getBody()->getContents();
            return json_decode($responseBody, true);
        } catch (\Exception $e) {
            Log::error('API Upload Error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
            ]);
            
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorResponse = json_decode($errorBody, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($errorResponse)) {
                    return $errorResponse;
                }
            }
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Execute POST request with multipart data
     *
     * @param string $endpoint
     * @param array $multipartData
     * @return array
     */
    public function postMultipart($endpoint, $multipartData = [])
    {
        try {
            // Log URL yang akan dipanggil
            $fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
            Log::info('Making POST multipart request to: ' . $fullUrl, ['multipart_count' => count($multipartData)]);
            
            // Create client without Content-Type header for multipart
            $multipartClient = new Client([
                'base_uri' => rtrim($this->baseUrl, '/') . '/',
                'timeout' => 60, // Longer timeout for uploads
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => Session::get('api_token') ? 'Bearer ' . Session::get('api_token') : '',
                ],
            ]);
            
            $response = $multipartClient->post(ltrim($endpoint, '/'), [
                'multipart' => $multipartData,
            ]);
            
            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true);
            
            // Periksa apakah JSON decode berhasil
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResponse)) {
                return $decodedResponse;
            } else {
                // Jika JSON tidak valid, return error
                Log::error('Invalid JSON response from API multipart request', [
                    'endpoint' => $endpoint,
                    'response_body' => $responseBody,
                    'json_error' => json_last_error_msg()
                ]);
                
                return [
                    'status' => 'error',
                    'message' => 'Respons server tidak valid',
                ];
            }
        } catch (\Exception $e) {
            Log::error('API POST multipart Error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'multipart_count' => count($multipartData),
                'error' => $e->getMessage(),
            ]);
            
            // Periksa apakah exception memiliki response (untuk HTTP errors)
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorResponse = json_decode($errorBody, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($errorResponse)) {
                    return $errorResponse;
                }
            }
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Make direct HTTP request to specific URL
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array
     */
    public function makeRequest($method, $url, $options = [])
    {
        try {
            $client = new Client([
                'timeout' => 30,
                'verify' => false,
            ]);

            $response = $client->request($method, $url, $options);
            $body = $response->getBody()->getContents();
            
            Log::info('Direct API Request', [
                'method' => $method,
                'url' => $url,
                'status' => $response->getStatusCode(),
                'response_body' => substr($body, 0, 500) // Log first 500 chars
            ]);
            
            return json_decode($body, true) ?? [];
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Direct API Client Error', [
                'method' => $method,
                'url' => $url,
                'status' => $e->getResponse()->getStatusCode(),
                'error' => $e->getMessage()
            ]);
            
            $body = $e->getResponse()->getBody()->getContents();
            $errorResponse = json_decode($body, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($errorResponse)) {
                return $errorResponse;
            }
            
            return [
                'status' => 'error',
                'message' => 'HTTP ' . $e->getResponse()->getStatusCode() . ': ' . $e->getMessage(),
            ];
            
        } catch (\Exception $e) {
            Log::error('Direct API Request Error', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada request: ' . $e->getMessage(),
            ];
        }
    }
}
