<x-customer-account-layout title="Đơn hàng của tôi" :flush="true">
    <div class="p-4 sm:p-8">
        <div class="min-w-0">
            @include('orders.index', ['routePrefix' => 'customer.orders', 'isStaff' => false])
        </div>
    </div>
</x-customer-account-layout>
