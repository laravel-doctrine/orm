@if(isset($sourceArray['custom_types']))
    'custom_types' => [
    @foreach($sourceArray['custom_types'] as $key => $val)
        '{{$key}}' => '{{$val}}'
    @endforeach
    ],
@endif
