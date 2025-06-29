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
            
            return json_decode($response->getBody()->getContents(), true);
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
}
