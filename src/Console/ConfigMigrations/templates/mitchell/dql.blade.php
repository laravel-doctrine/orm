@if(\LaravelDoctrine\ORM\Utilities\ArrayUtil::get($dql['datetime_functions']) !== null)
    'custom_datetime_functions' => [
    @foreach($dql['datetime_functions'] as $key => $val)
        '{{$key}}' => '{{$val}}'
        @endforeach
    ],
@endif
@if(\LaravelDoctrine\ORM\Utilities\ArrayUtil::get($dql['numeric_functions']) !== null)
    'custom_numeric_functions' => [
    @foreach($dql['numeric_functions'] as $key => $val)
        '{{$key}}' => '{{$val}}'
    @endforeach
    ],
@endif
@if(\LaravelDoctrine\ORM\Utilities\ArrayUtil::get($dql['string_functions']) !== null)
    'custom_string_functions' => [
    @foreach($dql['string_functions'] as $key => $val)
        '{{$key}}' => '{{$val}}'
    @endforeach
    ],
@endif
