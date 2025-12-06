@extends('layouts.app')

@section('title', 'Календарь активности - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">📅 Календарь активности</h1>
    
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold mb-4 text-gray-300">Последний год</h2>
            <p class="text-gray-400 mb-4">Каждый квадрат представляет один день. Чем темнее цвет, тем больше повторений было сделано.</p>
        </div>
        
        <div class="grid grid-cols-53 gap-1 mb-4" id="calendar-grid">
            @php
                $startDate = $today->copy()->subYear();
                $currentDate = $startDate->copy();
                $maxCount = $repetitionDates->max('count') ?? 1;
            @endphp
            
            @for($i = 0; $i < 53; $i++)
                <div class="flex flex-col gap-1">
                    @for($j = 0; $j < 7; $j++)
                        @php
                            $dateKey = $currentDate->format('Y-m-d');
                            $repetition = $repetitionDates->get($dateKey);
                            $count = $repetition ? $repetition->count : 0;
                            $intensity = $maxCount > 0 ? min(4, floor(($count / $maxCount) * 4)) : 0;
                            $isToday = $currentDate->isSameDay($today);
                            $displayDate = $currentDate->copy();
                            $currentDate->addDay();
                        @endphp
                        <div 
                            class="w-3 h-3 rounded-sm calendar-day 
                                @if($intensity == 0) bg-gray-700 
                                @elseif($intensity == 1) bg-purple-600 
                                @elseif($intensity == 2) bg-purple-500 
                                @elseif($intensity == 3) bg-purple-400 
                                @else bg-purple-300 
                                @endif
                                @if($isToday) ring-2 ring-purple-400 ring-offset-2 ring-offset-gray-800 @endif
                                hover:ring-2 hover:ring-purple-300 cursor-pointer"
                            data-date="{{ $displayDate->format('Y-m-d') }}"
                            data-count="{{ $count }}"
                            title="{{ $displayDate->format('d.m.Y') }}: {{ $count }} повторений">
                        </div>
                    @endfor
                </div>
            @endfor
        </div>
        
        <div class="flex items-center justify-between mt-6">
            <div class="flex items-center gap-4">
                <span class="text-gray-400 text-sm">Меньше</span>
                <div class="flex gap-1">
                    <div class="w-3 h-3 bg-gray-700 rounded-sm"></div>
                    <div class="w-3 h-3 bg-purple-600 rounded-sm"></div>
                    <div class="w-3 h-3 bg-purple-500 rounded-sm"></div>
                    <div class="w-3 h-3 bg-purple-400 rounded-sm"></div>
                    <div class="w-3 h-3 bg-purple-300 rounded-sm"></div>
                </div>
                <span class="text-gray-400 text-sm">Больше</span>
            </div>
            
            <div class="text-gray-400 text-sm">
                Всего повторений: <span class="text-purple-400 font-bold">{{ $repetitionDates->sum('count') }}</span>
            </div>
        </div>
    </div>
    
    <div class="mt-6">
        <a href="{{ route('study.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
            Начать повторение
        </a>
    </div>
</div>

@push('styles')
<style>
    .grid-cols-53 {
        grid-template-columns: repeat(53, minmax(0, 1fr));
    }
    
    @media (max-width: 768px) {
        .grid-cols-53 {
            grid-template-columns: repeat(26, minmax(0, 1fr));
        }
    }
</style>
@endpush
@endsection

