<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HandleRangeRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Обрабатываем только запросы к аудио файлам
        if ($response instanceof BinaryFileResponse && $request->hasHeader('Range')) {
            $file = $response->getFile();
            $fileSize = $file->getSize();
            $range = $request->header('Range');

            if ($range && preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = (int) $matches[1];
                $end = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $fileSize - 1;

                if ($start > $end || $start < 0 || $end >= $fileSize) {
                    return response('Range Not Satisfiable', 416, [
                        'Content-Range' => "bytes */{$fileSize}",
                    ]);
                }

                $length = $end - $start + 1;
                $fileHandle = fopen($file->getPathname(), 'rb');
                fseek($fileHandle, $start);

                return response()->stream(function () use ($fileHandle, $length) {
                    echo fread($fileHandle, $length);
                    fclose($fileHandle);
                }, 206, [
                    'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
                    'Content-Length' => $length,
                    'Content-Type' => $response->headers->get('Content-Type'),
                    'Accept-Ranges' => 'bytes',
                ]);
            }
        }

        // Для всех остальных ответов добавляем заголовок Accept-Ranges
        if ($response->headers->has('Content-Type') && 
            str_contains($response->headers->get('Content-Type'), 'audio/')) {
            $response->headers->set('Accept-Ranges', 'bytes');
        }

        return $response;
    }
}

