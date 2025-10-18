<x-filament-panels::page>
    <div class="filament-forms-card p-6 rounded-xl bg-white shadow">
        <form wire:submit.prevent="validate">
            {{ $this->form }}
        </form>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <div class="filament-forms-card p-6 rounded-xl bg-white shadow">
            <h3 class="text-xl font-semibold mb-4">Total Pemasukan</h3>
            <p class="text-3xl text-green-600 font-bold">
                Rp{{ number_format($this->getTotalIncome(), 0, ',', '.') }}
            </p>
            <h4 class="text-lg font-semibold mt-6">Rincian Per Kategori:</h4>
            <ul class="list-disc list-inside mt-2">
                @foreach($this->getIncomeReport() as $category => $amount)
                <li>{{ $category }}: Rp{{ number_format($amount, 0, ',', '.') }}</li>
                @endforeach
            </ul>
        </div>

        <div class="filament-forms-card p-6 rounded-xl bg-white shadow">
            <h3 class="text-xl font-semibold mb-4">Total Pengeluaran</h3>
            <p class="text-3xl text-red-600 font-bold">
                Rp{{ number_format($this->getTotalExpense(), 0, ',', '.') }}
            </p>
            <h4 class="text-lg font-semibold mt-6">Rincian Per Kategori:</h4>
            <ul class="list-disc list-inside mt-2">
                @foreach($this->getExpenseReport() as $category => $amount)
                <li>{{ $category }}: Rp{{ number_format($amount, 0, ',', '.') }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mt-8">
        <div class="filament-forms-card p-6 rounded-xl bg-white shadow">
            <h3 class="text-xl font-semibold mb-4">Daftar Transaksi</h3>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>