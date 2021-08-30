<?php

namespace App\Http\Controllers;

use App\Clients\DaDataClient;
use App\Exceptions\ServiceException;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Validator;
use Auth;

class ApiController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            'email'  => [
                'required',
                'max:255',
                'email:rfc,dns',
                Rule::unique('users'),
            ],
            'inn' => [
                'required',
                'digits:10',
                Rule::unique('users'),
            ],
            'password' => 'required|min:4'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $data = $validator->getData();

        try {
            $companyCheck = DaDataClient::getInstance()->getCompanyByInn($data['inn']);
        } catch (ConnectException $exception) {
            return $this->error("Company checking service is unavailable", 500);
        } catch (ServiceException $exception) {
            return response()->json([
                'message' => null,
                'fields' => [
                    ['field' => 'inn', 'error' => $exception->getMessage()]
                ]
            ], 400);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }

        if (!$companyCheck['is_operating']) {
            return response()->json([
                'message' => null,
                'fields' => [
                    ['field' => 'inn', 'error' => "Company isn't operating"]
                ]
            ], 400);
        }

        $companyName = $companyCheck['name'];


        $user = new User();
        $user->email = $data['email'];
        $user->inn = $data['inn'];
        $user->password = Hash::make($data['password']);
        $user->company_name = $companyName;
        $user->name = $companyName;
        $user->save();
        $token = JWTAuth::attempt(['email' => $user->email, 'password' => $data['password']]);

        return $this->success(['success' => true, 'token' => $token, 'id' => $user->id]);
    }

    /**
     * @param $userId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userById($userId, Request $request) {
        /** @var User $user */
        $user = User::query()
            ->where('id', '=', $userId)
            ->first();

        if (!$user) {
            return $this->error("User #{$userId} not found!");
        }

        return $this->success([
            'id' => $user->id,
            'inn' => $user->inn,
            'name' => $user->company_name
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request) {
        $token = $request->header( 'Authorization' );
        JWTAuth::parseToken()->invalidate( $token );

        return $this->success(['success' => true]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request) {

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->error("Login credentials are invalid.");
            }
        } catch (JWTException $e) {
            return $this->error("Could not create token.");
        }

        $currentUser = Auth::user();

        return $this->success([
            'success' => true,
            'token' => $token,
            'id' => $currentUser->id,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request) {
        try {
            $token = JWTAuth::parseToken()->refresh();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => 'Token is Invalid'], 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => 'Token is Expired'], 401);
            }else{
                return response()->json(['status' => 'Authorization Token not found'], 401);
            }
        }

        return $this->success([
            'success' => true,
            'token' => $token
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function check() {
        $currentUser = Auth::user();
        return $this->success(['success' => true, 'id' => $currentUser->id]);
    }

    /**
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data) {
        return response()->json($data, 200);
    }

    /**
     * @param MessageBag $messageBag
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationError(MessageBag $messageBag) {
        $messages = $messageBag->getMessages();
        $fields = (new Collection($messages))->map(function($values, $field) {
            return ['field' => $field, 'error' => implode("; ", $values)];
        })->toArray();

        return response()->json(['message' => null, 'fields' => $fields], 400);
    }

    /**
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message, $code = 400) {
        return response()->json(['message' => $message], $code);
    }
}
