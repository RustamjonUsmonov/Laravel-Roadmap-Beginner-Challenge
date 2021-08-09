<tr class="text-gray-700">
    <td class="px-4 py-3 border">
        <div class="flex items-center text-sm">
            <div class="relative w-8 h-8 mr-3 rounded-full md:block">
                <img class="object-cover w-full h-full rounded-full"
                     src="{{$article->image}}"
                     alt="" loading="lazy"/>
                <div class="absolute inset-0 rounded-full shadow-inner"
                     aria-hidden="true"></div>
            </div>
            <div>
                <p class="font-semibold text-black">{{$article->title}}</p>
                <p class="text-xs text-gray-600">{{$article->tags_count}} {{Str::plural('tag',$article->tags_count)}}</p>
            </div>
        </div>
    </td>
    <td class="px-4 py-3 text-ms font-semibold border">{{str_word_count($article->fulltext)}}</td>
    <td class="px-4 py-3 text-sm border">{{$article->created_at->format('d/m/Y H:i')}}</td>
</tr>
