<x-filament-panels::page>
    
    {{-- TAMPILKAN NOTIFIKASI BUDGET DI SINI --}}
    @php
        // 1. Panggil notifikasi dari halaman dashboard
        $notifications = $this->getBudgetNotifications();
    @endphp

    @if (!empty($notifications))
        <div class="space-y-4 mb-6">
            @foreach ($notifications as $notification)
                @php
                    // 2. Tentukan variabel warna di sini (HANYA LOGIKA PHP)
                    $borderColor = '#4CAF50'; // success (default)
                    $bgColor = '#F1F8F4'; // background hijau muda
                    
                    if ($notification['color'] === 'danger') {
                        $borderColor = '#F44336';
                        $bgColor = '#FFEBEE'; // background merah muda
                    } elseif ($notification['color'] === 'warning') {
                        $borderColor = '#FF9800';
                        $bgColor = '#FFF3E0'; // background orange muda
                    }
                @endphp
                
                <div 
                    class="p-4 rounded-lg shadow-md"
                    style="border-left: 5px solid <?php echo $borderColor; ?>; background-color: <?php echo $bgColor; ?>;"
                >
                    <h3 class="text-lg font-semibold text-gray-900">{{ $notification['title'] }}</h3>
                    <p class="text-sm text-gray-700 mt-1">{{ $notification['message'] }}</p>
                </div>
            @endforeach
        </div>
    @endif
    
    {{ $this->headerWidgets }}

    {{ $this->content }}
</x-filament-panels::page>