@if(\LaravelDoctrine\ORM\Utilities\ArrayUtil::get($dql['custom_datetime_functions']) !== null)
    'custom_datetime_functions' => [
    @foreach($dql['custom_datetime_functions'] as $key => $val)
        '{{$key}}' => '{{$val}}',
        @endforeach
    ],
@endif
@if(\LaravelDoctrine\ORM\Utilities\ArrayUtil::get($dql['custom_numeric_functions']) !== null)
    'custom_numeric_functions' => [
    @foreach($dql['custom_numeric_functions'] as $key => $val)
        '{{$key}}' => '{{$val}}',
    @endforeach
    ],
@endif
@if(\LaravelDoctrine\ORM\Utilities\ArrayUtil::get($dql['custom_string_functions']) !== null)
    'custom_string_functions' => [
    @foreach($dql['custom_string_functions'] as $key => $val)
        '{{$key}}' => '{{$val}}',
    @endforeach
    ],
@endif
