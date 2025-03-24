
<?php
// Array of Indian states and their districts/cities
$locations = [
    'Karnataka' => [
        'Bangalore' => ['Bangalore City', 'Electronic City', 'Whitefield'],
        'Mysore' => ['Mysore City', 'Chamundi Hills', 'Hebbal'],
        'Mangalore' => ['Mangalore City', 'Ullal', 'Surathkal']
    ],
    'Maharashtra' => [
        'Mumbai' => ['Mumbai City', 'Andheri', 'Bandra'],
        'Pune' => ['Pune City', 'Hinjewadi', 'Kothrud'],
        'Nagpur' => ['Nagpur City', 'Civil Lines', 'Sadar']
    ],
    // Add more states as needed
];

if(isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'getStates':
            echo json_encode(array_keys($locations));
            break;
        
        case 'getDistricts':
            $state = $_POST['state'];
            if(isset($locations[$state])) {
                echo json_encode(array_keys($locations[$state]));
            }
            break;
            
        case 'getCities':
            $state = $_POST['state'];
            $district = $_POST['district'];
            if(isset($locations[$state][$district])) {
                echo json_encode($locations[$state][$district]);
            }
            break;
    }
    exit;
}
?>
