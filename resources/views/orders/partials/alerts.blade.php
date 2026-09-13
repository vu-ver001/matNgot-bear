{{-- Flash success/error đã chuyển sang toast góc phải (toastManager), chỉ giữ $errors validation ở đây. --}}
@if (isset($errors) && $errors->any())
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
