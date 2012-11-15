super-var-dump
==============

A customized var_dump walker for viewing complex PHP variable data with an easy, javascript-backed nested-exploring view.

### Example Usage

    $example_object->example_array = array(1,2,3);
    $example_object->int = 3;
    $example_object->multi_dimensional_array =array(
        'another_array' => array(4,5,6)
    );

    super_var_dump($example_object);
    // outputs nested tree of variable data. 