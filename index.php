<?php

require 'vendor/autoload.php';
require 'functions.php';
require 'config.php';

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="Tax Code Service", version="1.0.0")/**
 * @OA\Server(url=API_HOST)
 */
Flight::route('/', function(){
	header("Location: " . API_HOST . "/swagger");
	exit();
});

/**
 * @OA\Get(
 *     path="/extract/{taxCode}",
 *     @OA\Parameter(
 *         name="taxCode",
 *         in="path",
 *         description="Italian tax code to extract",
 *         required=true,
 *         @OA\Schema(
 *             default="RSSMRA80A41F205B",
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
  *                  @OA\Property(
 *                      property="dateOfBirth",
 *                      type="string",
 *                      example="01/01/1980",
 *                      description="Date of Birth"
 *                  ),
 *                  @OA\Property(
 *                      property="cityOfBirth",
 *                      type="string",
 *                      example="Milano",
 *                      description="City of Birth"
 *                  ),
 *                  @OA\Property(
 *                      property="gender",
 *                      type="string",
 *                      example="M",
 *                      description="Gender ('M' or 'F')"
 *                  )
 *              ),
 *          )
 *     ),
 *     @OA\Response(response="404", description="Resource not find")
 * )
 */
Flight::route('GET /extract/@taxCode', function($taxCode)
{
    try {
        if (isset($taxCode) && (!empty($taxCode)))
        {            
            /* Validation */
            if (!validate_tax_code($taxCode))
            {
                Flight::json("Format not valid", $code = 400);
                return;
            }

            $data = array(
                'gender' => get_gender($taxCode),
                'dateOfBirth' => get_date_of_birth($taxCode),
                'cityOfBirth' => get_city_of_birth($taxCode)
            );
            Flight::json($data);
        }
        else
        {
            Flight::json("Unsupported Media Type", $code = 415);
        }
    }
    catch (Exception $err)
    {
        Flight::error($err);
    }
});

/**
 * @OA\Post(
 *     path="/generate",
 *     @OA\RequestBody(
 *         description="Generate italian tax code",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  required={"name", "surname", "dateOfBirth", "cityOfBirth", "gender"},
 *                  @OA\Property(
 *                      property="name",
 *                      type="string",
 *                      example="MARIO",
 *                      description="Name"
 *                  ),
 *                  @OA\Property(
 *                      property="surname",
 *                      type="string",
 *                      example="ROSSI",
 *                      description="Surname"
 *                  ),
 *                  @OA\Property(
 *                      property="dateOfBirth",
 *                      type="string",
 *                      example="01/01/1980",
 *                      description="Date of Birth"
 *                  ),
 *                  @OA\Property(
 *                      property="cityOfBirth",
 *                      type="string",
 *                      example="Milano",
 *                      description="City of Birth"
 *                  ),
 *                  @OA\Property(
 *                      property="gender",
 *                      type="string",
 *                      example="M",
 *                      enum = {"M", "F"},
 *                      description="Gender ('M' or 'F')"
 *                  )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\Schema(
 *              @OA\Property(
 *                  property="taxCode",
 *                  type="string",
 *                  example="RSSMRA80A41F205B",
 *                  description="The italian tax code"
 *              )
 *          )
 *     ),
 *     @OA\Response(response="404", description="Resource not find")
 * )
 */
Flight::route('POST /generate', function()
{
    try {
        $obj = json_decode(file_get_contents("php://input"));
        if (isset($obj))
        {
            /* Validate */
            if (!validate_request($obj))
            {
                Flight::json("Request not valid", $code = 400);
                return;                
            }

            /* Generate */
            $tax_code = generate_tax_code($obj);

            $data = array(
                'taxCode' => $tax_code
            );
            Flight::json($data);
        }
        else
        {
            Flight::json("Unsupported Media Type", $code = 415);
        }
    }
    catch (Exception $err)
    {
        Flight::error($err);
    }
});

Flight::start();

?>