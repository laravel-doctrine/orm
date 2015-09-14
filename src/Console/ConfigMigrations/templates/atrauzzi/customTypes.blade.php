@if(\LaravelDoctrine\ORM\Utilities\ArrayUtil::get($sourceArray['custom_types']) !== null)
    'custom_types' => [
    @foreach($sourceArray['custom_Types'] as $key => $val)
        '{{$key}}' => '{{$val}}'
    @endforeach
    ],
@endif