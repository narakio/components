@if(!is_null($media))
    <figure>
        @if(isset($alt))
            <img class="lazy" src="{{placeholder_image()}}" data-src="{{asset($media)}}" alt="{{$alt}}"/>
        @else
            <img src="{{asset($media)}}"/>
        @endif
    </figure>
@else
    <figure>
        @if(isset($format))
            @if(isset($alt))
                <img class="lazy" src="{{placeholder_image()}}" data-src="{{asset(sprintf('/media/img/site/placeholder_%s.png',\Naraki\Media\Models\MediaImgFormat::getFormatAcronyms($format)))}}"
                     alt="{{isset($alt)??''}}"/>
            @else
                <img src="{{asset(sprintf('/media/img/site/placeholder_%s.png',\Naraki\Media\Models\MediaImgFormat::getFormatAcronyms($format)))}}"/>
            @endif
        @else
            @if(isset($alt))
                <img src="{{asset(sprintf('/media/img/site/placeholder_%s.png',\Naraki\Media\Models\MediaImgFormat::getFormatAcronyms(\Naraki\Media\Models\MediaImgFormat::FEATURED)))}}"
                     alt="{{isset($alt)??''}}"/>
            @else
                <img src="{{asset(sprintf('/media/img/site/placeholder_%s.png',\Naraki\Media\Models\MediaImgFormat::getFormatAcronyms(\Naraki\Media\Models\MediaImgFormat::FEATURED)))}}"/>
            @endif
        @endif
    </figure>
@endif