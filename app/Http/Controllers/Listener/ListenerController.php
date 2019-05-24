<?php

namespace App\Http\Controllers\Listener;

//use function App\Http\Controllers\Listener\json as jsonAlias;
use App\Books;
use App\Http\Requests\Listeners;
use Illuminate\Http\Request;
use GuzzleHttp;
use App\Helpers\CoreHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use function MongoDB\BSON\toJSON;

class ListenerController extends Controller
{
    //

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws GuzzleHttp\Exception\GuzzleException
     */

    public function external_books(Request $request)
    {
//        Get Query Values From Request
        $name_of_books = $request['name'];

//  Make External Api Call to Ice And Fire For Data
        $client = new \GuzzleHttp\Client();
        $Guzzle_response = $client->request('GET', 'https://www.anapioficeandfire.com/api/books');

// Check Just in case Data Provider Is not Available
        if ($Guzzle_response->getStatusCode() === 200) {

            $data = json_decode($Guzzle_response->getBody(), true);
            $result = array();
            foreach ($data as $row) {
                if ($row['name'] == $name_of_books) {
                    array_push($result, $row);
                }
            }
            $arr_data = $result;
            $new_response_array = CoreHelper::doBeforeTask($arr_data);

            $response["status_code"] = 201;
            $response["status"] = 'Success';
            $response["message"] = CoreHelper::$arrStringResponses["name_of_books_found"];
            $response["data"] = $new_response_array;
            $errorCode = 200;
            return \Response::json($response, $errorCode);

        }
//        FallBack If Provider is Unreachable
        $response["status_code"] = 201;
        $response["status"] = 'Success';
        $response["message"] = CoreHelper::$arrStringResponses["provider_failure"];
        $response["data"] = [];
        $errorCode = 200;
        return \Response::json($response, $errorCode);
    }


    /**
     * @param Request $request
     * @return array
     */
    public function Dump_data_into_Books(Request $request)
    {
        $response = [];
        $errorCode = 400;

//        Call For Request Validation & Check
        $validation_message = CoreHelper::validate_incoming_request($request);

//      Check Validation Status And Output Messages
        if ($validation_message['status'] == false) {
            $response["message"] = $validation_message;
            return $response;
        }

        //       Check and Disallow Duplicate Entries
        $validation_message = CoreHelper::check_for_duplicate_entries($request);

        if ($validation_message === true) {

// Array Of Values to pump db
            $incoming_data['books'] = [
                'name' => $request['name'],
                'isbn' => $request['isbn'],
                'authors' => json_encode($request['authors']),
                'country' => $request['country'],
                'number_of_pages' => $request['number_of_pages'],
                'publisher' => $request['publisher'],
                'realease_date' => $request['realease_date'],
            ];

//            Insert into Db
            Books::insert($incoming_data['books']);

            $response["status_code"] = 201;
            $response["status"] = 'Success';
            $response["message"] = CoreHelper::$arrStringResponses["success_query"];
            $response["data"] = $incoming_data;
            $errorCode = 200;

        }

        $response["status"] = false;
        $response['message'] = $validation_message;


        return \Response::json($response, $errorCode);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_all_books(Request $request)
    {
        $response = [];
        $errorCode = 400;

        $get_all_books = Books::query()->get();
        $response["status_code"] = 201;
        $response["status"] = 'Success';
        $response["message"] = CoreHelper::$arrStringResponses["data_fetched"];
        $response["data"] = $get_all_books;
        $errorCode = 200;
        return \Response::json($response, $errorCode);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_specific_book(Request $request, $id)
    {
        $response = [];
        $errorCode = 400;

//        Call For Request Validation & Check
        $validation_message = CoreHelper::validate_incoming_request($request);

//      Check Validation Status And Output Messages
        if ($validation_message['status'] == false) {
            $response["message"] = $validation_message;
            return $response;
        }

        //       Check and Disallow Duplicate Entries
        $validation_message = CoreHelper::check_for_duplicate_entries_on_update($request);

        if ($validation_message === true) {

            // Array Of Values to pump db
            $incoming_data = [
                'name' => $request['name'],
                'isbn' => $request['isbn'],
                'authors' => json_encode($request['authors']),
                'country' => $request['country'],
                'number_of_pages' => $request['number_of_pages'],
                'publisher' => $request['publisher'],
                'realease_date' => $request['realease_date'],
            ];

            //            Update Db
            Books::where('id', $id)->update($incoming_data);

            $response["status_code"] = 200;
            $response["status"] = 'Success';
            $response["message"] = CoreHelper::$arrStringResponses["updated_query"];
            $response["data"] = $incoming_data;
            $errorCode = 200;
            return \Response::json($response, $errorCode);
        }

        $response["status"] = true;
        $response['message'] = $validation_message;


        return \Response::json($response, $errorCode);


    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_specific_book(Request $request, $id)
    {
        $response = [];
        $errorCode = 400;

        if (!$id) {
            $response["status_code"] = 200;
            $response["status"] = 'Success';
            $response["message"] = CoreHelper::$arrStringResponses["data_required"];
            $errorCode = 200;
            return \Response::json($response, $errorCode);
        }

            //       Check if data Exist Before Delete
            $validation_message = CoreHelper::check_before_delete($id);


            if ($validation_message === true) {

                $query = Books::query()->where('id', $id)->delete();

                $response["status_code"] = 204;
                $response["status"] = 'Success';
                $response["message"] = CoreHelper::$arrStringResponses["deleted_query"];
                $response["data"] = $query;
                $errorCode = 200;
                return \Response::json($response, $errorCode);
            }

            $response["status"] = true;
            $response['message'] = $validation_message;


            return \Response::json($response, $errorCode);



    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show_specific_book(Request $request, $id)
    {
        $response = [];
        $errorCode = 400;

        if (!$id) {
            $response["status_code"] = 200;
            $response["status"] = 'Success';
            $response["message"] = CoreHelper::$arrStringResponses["data_required"];
            $errorCode = 200;
            return \Response::json($response, $errorCode);
        }

        //       Check if data Exist Before Delete
        $validation_message = CoreHelper::check_before_delete($id);

// Logic Reasoning
        if ($validation_message === true) {

        $get_all_books = Books::query()->where('id', $id)->get();
        $response["status_code"] = 200;
        $response["status"] = 'Success';
        $response["message"] = CoreHelper::$arrStringResponses["data_fetched"];
        $response["data"] = $get_all_books;
        $errorCode = 200;
        }
        $response["status"] = true;
        $response['message'] = $validation_message;


        return \Response::json($response, $errorCode);
    }
}
