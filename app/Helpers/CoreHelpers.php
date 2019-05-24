<?php
namespace App\Helpers;

use App\Books;
use App\Http\Requests\Listeners;
use Illuminate\Database\Eloquent\Model;


class CoreHelper extends Model
{

//    ALl Api Recompense In Array Format
    public static $arrStringResponses = array(
        "name_of_books_found"=>"Data Query Successfully Found",
        "provider_failure" => "Data Provider Unreachable Please Try Again",
        "required_data"=>"All Fields Are Required",
        "duplicate_data"=>"Data Already Exist",
        "success_query" => "Book Inserted Successfully",
        "updated_query" => "Books was updated successfully",
        "deleted_query" => "Books was deleted successfully",
        "data_fetched" => "Data Fetched Successfully",
        "invalid_data"=> "Data Does Not Exist",
        "data_required" => "Book id Is required to perform Operation"
    );



    /**
     * @param $arr_data
     * @return false|string
     */
    public static function doBeforeTask($arr_data) {
        $result = $arr_data;

        //Unset Specific Json Object From Array List
        unset($result[0]['characters']);
        unset($result[0]['povCharacters']);

//        Return New Json Data
        return $result;
    }


// Validate InComing Request
    public static function validate_incoming_request($request)
    {
        $response = [];
        //        Get Query Values From Request
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:posts|max:255',
            'isbn' => 'required',
            'authors' => 'required',
            'country' => 'required',
            'number_of_pages' => 'required',
            'publisher' => 'required',
            'realease_date' => 'required',
        ]);

        if ($validator->fails()) {
            $message = $validator->errors();
            $response["status"] = false;
            $response["message"] = $message;
            return $response;

        }
        $response["status"] = true;
        return $response;
    }


//    Check If Data Already Exist
    public static function check_for_duplicate_entries($request)
    {
       $query = Books::query()->where('name', $request['name'])->count();

//       Return Error Message If Greater Than 0
       if ($query !== 0){
           $response["status"] = false;
           $response["message"] = CoreHelper::$arrStringResponses['duplicate_data'];
           return $response;
       }

//       Return true if $query is 0
       return true;
    }


    public static function check_for_duplicate_entries_on_update($request)
    {
       $query = Books::query()->where('name', $request['name'])->count();

//       Return Error Message If Greater Than 0
       if ($query == 0){
           $response["status"] = false;
           $response["message"] = CoreHelper::$arrStringResponses['invalid_data'];
           return $response;
       }

//       Return true if $query is 0
       return true;
    }

    public static function check_before_delete($id)
    {
       $query = Books::query()->where('id', $id)->count();

//       Return Error Message If Greater Than 0
       if ($query == 0){
           $response["status"] = false;
           $response["message"] = CoreHelper::$arrStringResponses['invalid_data'];
           return $response;
       }

//       Return true if $query is 0
       return true;
    }
}
