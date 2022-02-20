<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Shop\Services\ShopServiceFactory;
use Modules\Order\Services\OrderServiceFactory;
use Modules\Common\Http\Controllers\CommonController;
use PeterPetrus\Auth\PassportToken;

class CartController extends CommonController
{
    public function search(Request $request)
    {
        $user = $request->user();
        try {
            $shops = CartServiceFactory::mCartService()->getByUser($user->id);
            return $this->sendResponse($shops, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function reUpdate($id)
    {
        try {
            $cart = CartServiceFactory::mCartService()->findById($id);
            if ($cart) {
                $cartItems = $cart['cart_items'];

                $tien_hang = 0;
                $count_product = 0;
                foreach ($cartItems as $cartItem) {
                    $price = self::convertPrice($cartItem['price']);
                    $rate = $cartItem['rate'];
                    $amount = $cartItem['amount'];
                    $tien_hang = $tien_hang + round($price * $rate * $amount);
                    $count_product = $count_product + $cartItem['amount'];
                }

                if ($tien_hang_old > 0) {
                    $phi_tt = round(($tien_hang * $phi_tt_old) / $tien_hang_old);
                } else {
                    if (!empty($order['user']['cost_percent'])) {
                        $tigia = $order['user']['cost_percent'];
                        $phi_tt = round($tien_hang * $tigia / 100);
                    } else {
                        $phi_tt = 0;
                    }
                }

                $orderInput = array();
                $orderInput['id'] = $input['order_id'];
                $orderInput['tien_hang'] = $tien_hang;
                $orderInput['phi_tam_tinh'] = $phi_tt;
                $orderInput['tong'] = $tien_hang + $phi_tt;
                $orderInput['count_product'] = $count_product;
                OrderServiceFactory::mOrderService()->update($orderInput);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function itemUpdate(Request $request)
    {
        $input = $request->all();
        DB::beginTransaction();
        try {
            $user = $request->user();
            $cartItem = CartServiceFactory::mCartService()->itemFindById($input['id']);
            if (empty($cartItem)) {
                return $this->sendError('Error', ['Không tồn tại sản phẩm!']);
            }

            $cart = CartServiceFactory::mCartService()->findById($input['cart_id']);
            if (empty($cart)) {
                return $this->sendError('Error', ['Không tồn tại giỏ hàng!']);
            }

            if ($cart['user_id'] != $user->id) {
                return $this->sendError('Error', ['Không có quyền sửa giỏ hàng!']);
            }

            if ($cart['status'] == 2) {
                return $this->sendError('Error', ['Không thể sửa giỏ hàng!']);
            }

            $update = CartServiceFactory::mCartService()->itemUpdate($input);
            if ($update) {
                self::reUpdate($cart['id']);
            }
            DB::commit();
            return $this->sendResponse($update, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function convertPrice($priceStr)
    {
        $price = str_replace(' ', '', $priceStr);
        $price = explode('-', $price)[0];
        $price = str_replace(',', '.', $price);
        return $price;
    }

    public function create(Request $request)
    {
        $input = $request->all();
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
            // return $this->sendResponse($decoded_token, 'Successfully.');
            $inputData = self::json_decode_nice($input['cart']);

            $rate = 0;
            $userData = CommonServiceFactory::mUserService()->findById($decoded_token['user_id']);
            if (!empty($userData) && !empty($userData['user']) && !empty($userData['user']['rate'])) {
                $rate = (int)$userData['user']['rate'];
            } else {
                $setting = CommonServiceFactory::mSettingService()->findByKey('rate');
                $rate = (int)$setting['setting']['value'];
            }

            foreach ((array)$inputData as $item) {
                $inputCart = (array)$item;
                $inputCart['rate'] = $rate;
                $arrRules = [
                    'amount' => 'required',
                    'domain' => 'required',
                    'image' => 'required',
                    'method' => 'required',
                    //'name' => 'required',
                    'pro_link' => 'required',
                    'rate' => 'required',
                    'site' => 'required'
                ];
                $arrMessages = [
                    'amount.required' => 'amount.required',
                    'domain.required' => 'domain.required',
                    'image.required' => 'image.required',
                    'method.required' => 'method.required',
                    //'name.required' => 'name.required',
                    'pro_link.required' => 'pro_link.required',
                    'rate.required' => 'rate.required',
                    'site.required' => 'site.required'
                ];

                $validator = Validator::make($inputCart, $arrRules, $arrMessages);
                if ($validator->fails()) {
                    return $this->sendError('Error', $validator->errors()->all());
                }

                // Shop
                $shop = ShopServiceFactory::mShopService()->findByUrl($inputCart['shop_link']);
                if (!$shop) {
                    $inputShop = [
                        'name' => $inputCart['shop_nick'],
                        'url' => $inputCart['shop_link']
                    ];
                    $shop = ShopServiceFactory::mShopService()->create($inputShop);
                    // return $this->sendError('Error', 'Shop.' . $inputCart['shop_nick'] . '.NotExit');
                }

                $inputCart['shop_id'] = $shop['id'];
                $inputCart['user_id'] = $decoded_token['user_id'];
                $inputCart['price'] = self::convertPrice($inputCart['price']);
                $inputCart['price_arr'] = json_encode($inputCart['price_arr']);
                $create = CartServiceFactory::mCartService()->create($inputCart);
            }

            return $this->sendResponse(1, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function json_decode_nice($json, $assoc = FALSE)
    {
        $json = str_replace(array("\n", "\r"), "", $json);
        //$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', $json);
        //$json = preg_replace('/(,)\s*}$/', '}', $json);
        return json_decode($json, $assoc);
    }

    public function delete(Request $request)
    {
        $input = $request->all();
        $arrId = explode(',', $input['ids']);
        $carts = CartServiceFactory::mCartService()->findByIds($arrId);
        $deleteData = array();
        $errData = array();
        foreach ($arrId as $id) {
            $check = false;
            foreach ($carts as $cart) {
                if ($id == $cart['id']) {
                    $check = true;
                    $cart['is_deleted'] = 1;
                    $deleteData[] = $cart;
                }
            }
            if (!$check) {
                $errData[] = 'Cart Id ' . $id . ' NotExist';
            }
        }

        if (!empty($errData)) {
            return $this->sendError('Error', $errData);
        }

        try {
            CartServiceFactory::mCartService()->delete($arrId);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
