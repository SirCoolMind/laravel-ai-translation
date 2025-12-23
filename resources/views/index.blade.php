<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Translation Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

<div class="max-w-7xl mx-auto bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold mb-4">AI Translation Manager</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('ai-translation.store') }}" method="POST" class="mb-8 bg-gray-50 p-4 rounded border">
        @csrf
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-4">
                <label class="block text-sm font-medium text-gray-700">Key (e.g., welcome_msg)</label>
                <input type="text" name="key" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border">
            </div>
            <div class="col-span-6">
                <label class="block text-sm font-medium text-gray-700">Word (Source: {{ $source }})</label>
                <input type="text" name="word" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="Leave empty to use Key">
            </div>
            <div class="col-span-2">
                <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
                    Auto Translate
                </button>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                    @foreach($langs as $lang)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ strtoupper($lang) }}</th>
                    @endforeach
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($translations as $key => $vals)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $key }}</td>
                        @foreach($langs as $lang)
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ Str::limit($vals[$lang], 50) }}
                            </td>
                        @endforeach
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form action="{{ route('ai-translation.destroy') }}" method="POST" onsubmit="return confirm('Delete this key from ALL language files?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="key" value="{{ $key }}">
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
