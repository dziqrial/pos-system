@extends('layouts.app')

@section('title', 'Kitchen Display')
@section('page-title', 'Kitchen Display System')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6" x-data="kitchen()" x-init="startPolling()">

    <!-- PENDING -->
    <div>
        <div class="flex items-center gap-2 mb-3">
            <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
            <h3 class="font-semibold text-gray-700">Antrian ({{ ($orders['pending'] ?? collect())->count() }})</h3>
        </div>
        <div class="space-y-3">
            @foreach($orders['pending'] ?? [] as $order)
            <div class="bg-white rounded-xl border-2 border-yellow-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono text-sm font-bold text-gray-700">#{{ $order->id }}</span>
                    @if($order->table)
                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">{{ $order->table->name }}</span>
                    @endif
                </div>
                <div class="space-y-1 mb-3">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span class="{{ $item->status === 'done' ? 'line-through text-gray-400' : 'text-gray-700' }}">
                            {{ $item->transactionItem->name ?? 'Item' }} ×{{ $item->transactionItem->qty ?? 1 }}
                        </span>
                        @if($item->note)
                        <span class="text-xs text-orange-500">{{ $item->note }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                <button onclick="updateOrderStatus({{ $order->id }}, 'cooking')"
                        class="w-full py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Mulai Masak
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <!-- COOKING -->
    <div>
        <div class="flex items-center gap-2 mb-3">
            <div class="w-3 h-3 bg-blue-400 rounded-full animate-pulse"></div>
            <h3 class="font-semibold text-gray-700">Sedang Dimasak ({{ ($orders['cooking'] ?? collect())->count() }})</h3>
        </div>
        <div class="space-y-3">
            @foreach($orders['cooking'] ?? [] as $order)
            <div class="bg-white rounded-xl border-2 border-blue-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono text-sm font-bold text-gray-700">#{{ $order->id }}</span>
                    @if($order->table)
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">{{ $order->table->name }}</span>
                    @endif
                </div>
                <div class="space-y-1 mb-3">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span class="{{ $item->status === 'done' ? 'line-through text-gray-400' : 'text-gray-700' }}">
                            {{ $item->transactionItem->name ?? 'Item' }}
                        </span>
                        <span class="px-2 py-0.5 text-xs rounded {{ $item->status === 'done' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                            {{ $item->status === 'done' ? 'Selesai' : 'Proses' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                <button onclick="updateOrderStatus({{ $order->id }}, 'ready')"
                        class="w-full py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                    Siap Disajikan
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <!-- READY -->
    <div>
        <div class="flex items-center gap-2 mb-3">
            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
            <h3 class="font-semibold text-gray-700">Siap Saji ({{ ($orders['ready'] ?? collect())->count() }})</h3>
        </div>
        <div class="space-y-3">
            @foreach($orders['ready'] ?? [] as $order)
            <div class="bg-white rounded-xl border-2 border-green-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono text-sm font-bold text-gray-700">#{{ $order->id }}</span>
                    @if($order->table)
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">{{ $order->table->name }}</span>
                    @endif
                </div>
                <button onclick="updateOrderStatus({{ $order->id }}, 'served')"
                        class="w-full py-1.5 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                    Tandai Tersaji
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
function updateOrderStatus(orderId, status) {
    fetch(`/kitchen/${orderId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ status })
    }).then(() => location.reload());
}

function kitchen() {
    return {
        startPolling() {
            setInterval(() => location.reload(), 30000); // refresh every 30s
        }
    };
}
</script>
@endsection
