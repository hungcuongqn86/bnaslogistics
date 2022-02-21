<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Common\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Validator;
use Modules\Shop\Services\ShopServiceFactory;
use PeterPetrus\Auth\PassportToken;

class ShopController extends CommonController
{
    public function search(Request $request)
    {
        $input = $request->all();
        try {
            return $this->sendResponse(ShopServiceFactory::mShopService()->search($input), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            return $this->sendResponse(ShopServiceFactory::mShopService()->findById($id), 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $input = $request->all();
        // $input['tk'] = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE5N2FjYjdkMjEyNGU0NzAzNTg4MGNmMjE4NWU0OGRjMzg0OTY2ZWNhNjA5Mjc1ZDBjZWVkNDU3MjkwMTU2ZjFiZTZkY2ViYmQwYjJhZGUwIn0.eyJhdWQiOiIxIiwianRpIjoiYTk3YWNiN2QyMTI0ZTQ3MDM1ODgwY2YyMTg1ZTQ4ZGMzODQ5NjZlY2E2MDkyNzVkMGNlZWQ0NTcyOTAxNTZmMWJlNmRjZWJiZDBiMmFkZTAiLCJpYXQiOjE2NDU0NTU5ODksIm5iZiI6MTY0NTQ1NTk4OSwiZXhwIjoxNjc2OTkxOTg5LCJzdWIiOiIxNTMiLCJzY29wZXMiOltdfQ.t1-hCd9xR0kX_pB7uLmxvbFND0YqeeVDpBvNk1K9VhjI1eCyEUTGqUZ_4A8ewx-f3EIXNBGMpnzF6OiZLec1NQ_PM13wLWzzL7TZXI1ugnFwniLlMZpjRTFKCBnT37nMU-CClF64iqGNVuMpqSzcX71lQFskj5pWbF1wPAFeu1sxXCHKbxfDm0g_86uGvFv36Gg4qK78tyoQcUoGNkKQZyuWPICgJpKCwMCVJDXMt_fSgA-nqulPFEL2PbCwnwdjaEeJW8Ba4EBGdrtQ4otUHo-uzq9u2r4fc0vDgirEbNueB-0LIPp1chj6n-0Tkt2nRvyBVvr-BiWUs0q-cykKp5BLd-Emzrabd-SsU0OaSbMylUnfXAQHgliic0SLRaUqjcUVojj47f_rksTdTwis-BghutzSfmg-rLak2wFriFLmcA09oOPF8KTZr99q7Vw-jzU2WtL5ex_XALlzrxMSiWVE07TWf6fyqFg-VBbuGq1z4sqeru3kSfuKVARE12blcXOSh9LAUhU6jEslwRHN4eO5Aajwtw6VGiVnk9EyhqjdlvFwuU-CTFZwcq8aXNZIw0nfzX36AleZBzxNXyWWNYUO_vVaBbLWj8LjhjPI3TJ8Qoxz8sKrNMaqmvyuyHUdozYVN7PcUXKrRKJQ3VrteOfBroYuVE1nBAwu1G97Yo4';
        try {
            if (empty($input['tk'])) {
                return $this->sendError('Error', ['Auth'], 401);
            }
            $decoded_token = PassportToken::dirtyDecode(
                $input['tk']
            );
            if ($decoded_token['valid']) {
                // Check if token exists in DB (table 'oauth_access_tokens'), require \Illuminate\Support\Facades\DB class
                $token_exists = PassportToken::existsValidToken(
                    $decoded_token['token_id'],
                    $decoded_token['user_id']
                );

                if (!$token_exists) {
                    return $this->sendError('Error', ['Auth'], 401);
                }
            } else {
                return $this->sendError('Error', ['Auth'], 401);
            }

            $arrRules = [
                'name' => 'required',
                'url' => 'required'
            ];
            $arrMessages = [
                'name.required' => 'name.required',
                'url.required' => 'url.required'
            ];

            $validator = Validator::make($input, $arrRules, $arrMessages);
            if ($validator->fails()) {
                return $this->sendError('Error', $validator->errors()->all());
            }

            $shop = ShopServiceFactory::mShopService()->findByUrl($input['url']);
            if ($shop) {
                return $this->sendResponse($shop, 'Successfully.');
            } else {
                $create = ShopServiceFactory::mShopService()->create($input);
                return $this->sendResponse($create, 'Successfully.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
