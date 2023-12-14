<?php 
    $hostname = "localhost";
    $database = "dbexpense";
    $username = "root";
    $password = "";

	$db = new PDO("mysql:host=$hostname;dbname=$database",$username,$password);
	//initial response code
	// response code will be changed if the request goes into any of the process
	http_response_code(404);
	$response = new stdClass();

	{
		$jsonbody = json_decode(file_get_contents('php://input'));
	}

	if($_SERVER["REQUEST_METHOD"] == "POST"){
		try{
			$stmt = $db->prepare("INSERT INTO expenses (amount,`desc`,`dateTime`) VALUES
			(:amount,:desc,:dateTime)");
			$stmt->execute(array(':amount' => $jsonbody->amount, ':desc'=>
			$jsonbody->desc,':dateTime' => $jsonbody->dateTime));
			http_response_code(200);
			
		}catch(Exception $ee){
			http_response_code(500);
			$response['error'] = "Error occured ". $ee->getMessage();
		}
	}

	else if ($_SERVER["REQUEST_METHOD"] == "GET"){
		try{
			$stmt = $db->prepare("SELECT * FROM expenses");
			$stmt-> execute();
			$response =  $stmt->fetchAll(PDO::FETCH_ASSOC);
			http_response_code(200);

		}
		catch(Exception $ee){
			http_response_code(500);
			$response['error'] = "Error occured ". $ee->getMessage();
		}
	}
	
	
	else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
	    try {
	        $stmt = $db->prepare("UPDATE expenses SET amount=:amount,`desc`=:desc, dateTime=:dateTime WHERE id=:id");
	        $stmt->execute(array(':id' => $jsonbody->id, ':amount' => $jsonbody->amount, ':desc' => $jsonbody->desc, ':dateTime' => $jsonbody->dateTime));
	        http_response_code(200);
	    } catch (Exception $ee) {
	        http_response_code(500);
	        $response['error'] = "Error occurred: " . $ee->getMessage();
	    }
	}
	
   else if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // Parse the JSON request body
    $jsonbody = json_decode(file_get_contents("php://input"));

    // Ensure the required fields are present
    if (!isset($jsonbody->id)) {
        http_response_code(400);
        $response['error'] = "ID is required for the delete operation.";
        echo json_encode($response);
        exit();
    }

    try {
        // Delete the record from the database
        $stmt = $db->prepare("DELETE FROM expenses WHERE id = :id");
        $stmt->execute(array(':id' => $jsonbody->id));

        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
        } else {
            // If no rows were affected, the record might not exist
            http_response_code(404);
            $response['error'] = "Record not found for ID: " . $jsonbody->id;
        }
    } catch (Exception $ee) {
        http_response_code(500);
        $response['error'] = "Error occurred " . $ee->getMessage();
    }

    // Return the response as JSON
    echo json_encode($response);
   }
   
   	
	echo json_encode($response);
	exit();
?>