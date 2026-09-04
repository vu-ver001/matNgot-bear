<x-customer-account-layout title="Chi tiết đơn hàng" :flush="true">
    <div class="p-4 sm:p-8">
        <div class="min-w-0">
            @include('orders.show', ['routePrefix' => 'customer.orders', 'isStaff' => false])
        </div>
    </div>
</x-customer-account-layout>
