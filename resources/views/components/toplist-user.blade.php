<div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
    <x-avatar :image="$avatar" lg/>
    <div>
        <x-h4 class="dark:text-white uppercase">{{$name}}</x-h4>
        <p class="text-gray-500 dark:text-gray-400">{{$label}}: {{dutyTimeFormatter($value ?? 0)}}</p>
    </div>
</div>
