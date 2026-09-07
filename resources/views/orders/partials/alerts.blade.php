@if (session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
@endif
@if (isset($errors) && $errors->any())
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
