<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MonitoringController2 extends Controller
{
    private $baseUrl = "https://drone-under-water-default-rtdb.asia-southeast1.firebasedatabase.app";
    private $maxLogs = 100; // Batasi maksimal 100 data terbaru

    public function index()
    {
        return view('monitoring.index2');
    }

    public function getData()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/sensors/device_01.json");
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal mengambil data'], 500);
            }
            
            $data = $response->json();

            $labels = [];
            $phData = [];
            $turbidityData = [];
            $tdsData = [];

            if (isset($data['logs']) && is_array($data['logs'])) {
                $logs = array_values($data['logs']);
                // Ambil 10 log terbaru untuk chart
                $latestLogs = array_slice($logs, -10);

                foreach ($latestLogs as $log) {
                    $timestamp = $this->formatTimestampToWIB($log['timestamp'] ?? null);
                    $labels[] = $timestamp;
                    $phData[] = (float)($log['ph'] ?? 0);
                    $turbidityData[] = (float)($log['turbidity'] ?? 0);
                    $tdsData[] = (float)($log['tds'] ?? 0);
                }
            }

            // Ambil current data
            $currentData = $data['current'] ?? null;
            
            // Jika current data tidak ada, ambil dari log terbaru
            if (!$currentData && isset($logs) && !empty($logs)) {
                $lastLog = end($logs);
                $currentData = [
                    'ph' => $lastLog['ph'] ?? 0,
                    'tds' => $lastLog['tds'] ?? 0,
                    'turbidity' => $lastLog['turbidity'] ?? 0,
                    'timestamp' => $lastLog['timestamp'] ?? null
                ];
            }

            return response()->json([
                'current' => $currentData,
                'labels' => $labels,
                'ph' => $phData,
                'turbidity' => $turbidityData,
                'tds' => $tdsData,
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLogs(Request $request)
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/sensors/device_01/logs.json");
            
            if (!$response->successful()) {
                return response()->json(['data' => [], 'total' => 0]);
            }
            
            $logsRaw = $response->json();

            if (!$logsRaw || !is_array($logsRaw)) {
                return response()->json(['data' => [], 'total' => 0]);
            }

            // Balik urutan agar yang terbaru di atas
            $logs = array_reverse(array_values($logsRaw));
            
            // **AMBIL HANYA 100 DATA TERBARU** 🚀
            $logs = array_slice($logs, 0, $this->maxLogs);

            $formatted = collect($logs)->map(function($log) {
                $timestamp = $this->formatTimestampToWIB($log['timestamp'] ?? null);
                
                return [
                    'timestamp' => $timestamp,
                    'timestamp_raw' => $log['timestamp'] ?? null,
                    'ph' => (float)($log['ph'] ?? 0),
                    'tds' => (float)($log['tds'] ?? 0),
                    'turbidity' => (float)($log['turbidity'] ?? 0),
                ];
            });

            return response()->json([
                'data' => $formatted->values(),
                'total' => $formatted->count(),
                'limited' => true,
                'max_logs' => $this->maxLogs
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'total' => 0]);
        }
    }

    private function formatTimestampToWIB($ts)
    {
        if (!$ts) {
            return Carbon::now('Asia/Jakarta')->format('d-m-Y H:i:s');
        }
        
        try {
            // Set timezone ke WIB
            Carbon::setLocale('id');
            
            // CASE 1: Jika timestamp adalah string waktu saja (HH:MM atau HH:MM:SS)
            if (is_string($ts) && preg_match('/^(\d{1,2}):(\d{2})(:(\d{2}))?$/', $ts, $matches)) {
                $hour = (int)$matches[1];
                $minute = (int)$matches[2];
                $second = isset($matches[4]) ? (int)$matches[4] : 0;
                
                // Validasi jam
                if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                    // Gabungkan dengan tanggal hari ini (WIB)
                    return Carbon::now('Asia/Jakarta')->format('d-m-Y') . sprintf(' %02d:%02d:%02d', $hour, $minute, $second);
                }
            }
            
            // CASE 2: Jika timestamp adalah string datetime lengkap
            if (is_string($ts)) {
                // Coba parse dengan Carbon
                try {
                    return Carbon::parse($ts)->timezone('Asia/Jakarta')->format('d-m-Y H:i:s');
                } catch (\Exception $e) {
                    // Jika gagal, coba format lain
                }
            }
            
            // CASE 3: Jika timestamp numeric (Unix timestamp)
            if (is_numeric($ts)) {
                $tsNum = (float)$ts;
                
                // Handle milidetik (13 digit)
                if ($tsNum > 10000000000) {
                    return Carbon::createFromTimestamp($tsNum / 1000, 'Asia/Jakarta')->format('d-m-Y H:i:s');
                }
                
                // Handle detik (10 digit)
                if ($tsNum > 1000000000) {
                    return Carbon::createFromTimestamp($tsNum, 'Asia/Jakarta')->format('d-m-Y H:i:s');
                }
                
                // Handle timestamp kecil (mungkin dari Arduino)
                if ($tsNum > 0 && $tsNum < 1000000) {
                    // Anggap ini adalah detik dari epoch, tapi kemungkinan format lain
                    // Coba deteksi apakah ini timestamp valid
                    $date = Carbon::createFromTimestamp($tsNum, 'Asia/Jakarta');
                    if ($date->year > 2000) {
                        return $date->format('d-m-Y H:i:s');
                    }
                }
            }
            
            // Fallback: return current time WIB
            return Carbon::now('Asia/Jakarta')->format('d-m-Y H:i:s');
            
        } catch (\Exception $e) {
            return Carbon::now('Asia/Jakarta')->format('d-m-Y H:i:s');
        }
    }
}