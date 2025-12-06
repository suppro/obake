<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AudioController extends Controller
{
    /**
     * Обслуживание аудио файлов с поддержкой Range-запросов
     */
    public function serve(Request $request, string $path)
    {
        // Полный путь к файлу
        $filePath = storage_path('app/public/' . $path);
        
        // Проверяем существование файла
        if (!file_exists($filePath) || !is_file($filePath)) {
            abort(404);
        }
        
        $fileSize = filesize($filePath);
        $range = $request->header('Range');
        
        // Если Range-запрос отсутствует, возвращаем весь файл
        if (!$range) {
            return response()->file($filePath, [
                'Content-Type' => $this->getMimeType($filePath),
                'Content-Length' => $fileSize,
                'Accept-Ranges' => 'bytes',
            ]);
        }
        
        // Парсим Range-запрос
        if (!preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            return response('Range Not Satisfiable', 416, [
                'Content-Range' => "bytes */{$fileSize}",
            ]);
        }
        
        $start = (int) $matches[1];
        $end = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $fileSize - 1;
        
        // Проверяем валидность диапазона
        if ($start > $end || $start < 0 || $end >= $fileSize) {
            return response('Range Not Satisfiable', 416, [
                'Content-Range' => "bytes */{$fileSize}",
            ]);
        }
        
        $length = $end - $start + 1;
        
        // Открываем файл для чтения
        $file = fopen($filePath, 'rb');
        if (!$file) {
            abort(500, 'Cannot open file');
        }
        
        // Перемещаемся к началу диапазона
        fseek($file, $start);
        
        // Создаем потоковый ответ
        return response()->stream(function () use ($file, $length) {
            $remaining = $length;
            $chunkSize = 8192; // 8KB chunks
            
            while ($remaining > 0 && !feof($file)) {
                $readSize = min($chunkSize, $remaining);
                echo fread($file, $readSize);
                $remaining -= $readSize;
                
                // Сбрасываем буфер вывода для больших файлов
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
            
            fclose($file);
        }, 206, [
            'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
            'Content-Length' => $length,
            'Content-Type' => $this->getMimeType($filePath),
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
    
    /**
     * Определение MIME-типа файла
     */
    private function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'webm' => 'audio/webm',
            'm4a' => 'audio/mp4',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}

