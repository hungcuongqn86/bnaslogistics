<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Cart\Services\CartServiceFactory;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Services\CommonServiceFactory;
use Modules\Shop\Services\ShopServiceFactory;
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
                if (sizeof($cartItems) > 0) {
                    // Lay ti gia tu setting
                    $settingRate = CommonServiceFactory::mSettingService()->findByKey('rate');
                    $rate = (int)$settingRate['setting']['value'];

                    // Lay vip
                    $ck_dv = 0;
                    $vip = CommonServiceFactory::mVipService()->findById($cart['user']['vip']);
                    if (!empty($vip)) {
                        $ck_dv = $vip['ck_dv'];
                    }

                    // Lay bang gia dv
                    $serviceFee = CommonServiceFactory::mServiceFeeService()->getAll();

                    // Lay bang gia kiem dem
                    $inspectionFee = CommonServiceFactory::mInspectionFeeService()->getAll();

                    $tien_hang = 0;
                    $count_product = 0;
                    foreach ($cartItems as $cartItem) {
                        $price = self::convertPrice($cartItem['price']);
                        $amount = $cartItem['amount'];
                        $tien_hang = $tien_hang + round($price * $rate * $amount, 0);
                        $count_product = $count_product + $cartItem['amount'];
                    }

                    // Tinh phi dich vu
                    $phi_dat_hang_cs = 0;
                    foreach ($serviceFee as $feeItem) {
                        if ($feeItem->min_tot_tran * 1000000 <= $tien_hang) {
                            $phi_dat_hang_cs = $feeItem->val;
                            break;
                        }
                    }

                    $phi_dat_hang = round(($phi_dat_hang_cs * $tien_hang) / 100);
                    $ck_dv_tt = round(($phi_dat_hang * $ck_dv) / 100);
                    $phi_dat_hang_tt = $phi_dat_hang - $ck_dv_tt;

                    // Kiem dem
                    $phi_kiem_dem_cs = 0;
                    if ($cart['kiem_hang'] == 1) {
                        foreach ($inspectionFee as $feeItem) {
                            if ($feeItem->min_count <= $count_product) {
                                $phi_kiem_dem_cs = $feeItem->val;
                                break;
                            }
                        }
                    }

                    $phi_kiem_dem_tt = 0;
                    if ($phi_kiem_dem_cs != 0) {
                        $phi_kiem_dem_tt = $count_product * $phi_kiem_dem_cs;
                    }
					
					$vip = "0";
					if (isset($vip['id']) && (!empty($vip['id']))) {
						$vip = $vip['id'];
					}

                    $cart['count_product'] = $count_product;
                    $cart['tien_hang'] = $tien_hang;
                    $cart['vip_id'] = $vip;
                    $cart['ck_dv'] = $ck_dv;
                    $cart['ck_dv_tt'] = $ck_dv_tt;
                    $cart['phi_dat_hang_cs'] = $phi_dat_hang_cs;
                    $cart['phi_dat_hang'] = $phi_dat_hang;
                    $cart['phi_dat_hang_tt'] = $phi_dat_hang_tt;
                    $cart['phi_kiem_dem_cs'] = $phi_kiem_dem_cs;
                    $cart['phi_kiem_dem_tt'] = $phi_kiem_dem_tt;
                    $cart['ti_gia'] = $rate;
                    CartServiceFactory::mCartService()->update($cart);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update($id, Request $request)
    {
        $input = $request->all();
        DB::beginTransaction();
        try {
            $user = $request->user();
            $cart = CartServiceFactory::mCartService()->findById($id);
            if (empty($cart)) {
                return $this->sendError('Error', ['Không tồn tại giỏ hàng!']);
            }

            if ($cart['user_id'] != $user->id) {
                return $this->sendError('Error', ['Không có quyền sửa giỏ hàng!']);
            }

            if ($cart['status'] == 2) {
                return $this->sendError('Error', ['Không thể sửa giỏ hàng!']);
            }

            $cart['kiem_hang'] = (int)$input['kiem_hang'];
            $cart['dong_go'] = (int)$input['dong_go'];
            $cart['bao_hiem'] = (int)$input['bao_hiem'];
            $cart['chinh_ngach'] = (int)$input['chinh_ngach'];
            $cart['vat'] = (int)$input['vat'];

            $update = CartServiceFactory::mCartService()->update($cart);
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
        // $input['tk'] = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE5N2FjYjdkMjEyNGU0NzAzNTg4MGNmMjE4NWU0OGRjMzg0OTY2ZWNhNjA5Mjc1ZDBjZWVkNDU3MjkwMTU2ZjFiZTZkY2ViYmQwYjJhZGUwIn0.eyJhdWQiOiIxIiwianRpIjoiYTk3YWNiN2QyMTI0ZTQ3MDM1ODgwY2YyMTg1ZTQ4ZGMzODQ5NjZlY2E2MDkyNzVkMGNlZWQ0NTcyOTAxNTZmMWJlNmRjZWJiZDBiMmFkZTAiLCJpYXQiOjE2NDU0NTU5ODksIm5iZiI6MTY0NTQ1NTk4OSwiZXhwIjoxNjc2OTkxOTg5LCJzdWIiOiIxNTMiLCJzY29wZXMiOltdfQ.t1-hCd9xR0kX_pB7uLmxvbFND0YqeeVDpBvNk1K9VhjI1eCyEUTGqUZ_4A8ewx-f3EIXNBGMpnzF6OiZLec1NQ_PM13wLWzzL7TZXI1ugnFwniLlMZpjRTFKCBnT37nMU-CClF64iqGNVuMpqSzcX71lQFskj5pWbF1wPAFeu1sxXCHKbxfDm0g_86uGvFv36Gg4qK78tyoQcUoGNkKQZyuWPICgJpKCwMCVJDXMt_fSgA-nqulPFEL2PbCwnwdjaEeJW8Ba4EBGdrtQ4otUHo-uzq9u2r4fc0vDgirEbNueB-0LIPp1chj6n-0Tkt2nRvyBVvr-BiWUs0q-cykKp5BLd-Emzrabd-SsU0OaSbMylUnfXAQHgliic0SLRaUqjcUVojj47f_rksTdTwis-BghutzSfmg-rLak2wFriFLmcA09oOPF8KTZr99q7Vw-jzU2WtL5ex_XALlzrxMSiWVE07TWf6fyqFg-VBbuGq1z4sqeru3kSfuKVARE12blcXOSh9LAUhU6jEslwRHN4eO5Aajwtw6VGiVnk9EyhqjdlvFwuU-CTFZwcq8aXNZIw0nfzX36AleZBzxNXyWWNYUO_vVaBbLWj8LjhjPI3TJ8Qoxz8sKrNMaqmvyuyHUdozYVN7PcUXKrRKJQ3VrteOfBroYuVE1nBAwu1G97Yo4';
        DB::beginTransaction();
        try {
            if (empty($input['tk'])) {
                return $this->sendError('Error', ['Auth'], 401);
            }
            $decoded_token = PassportToken::dirtyDecode(
                $input['tk']
            );
            if ($decoded_token['valid']) {
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

            $cart = null;
            $inputData = (array)self::json_decode_nice($input['cart']);
            if (sizeof($inputData) > 0) {
                // Shop
                $shopNick = $inputData[0]->shop_nick;
                $shopLink = $inputData[0]->shop_link;
                $shop = ShopServiceFactory::mShopService()->findByUrl($shopLink, $decoded_token['user_id']);
                if (!$shop) {
                    $inputShop = [
                        'name' => $shopNick,
                        'url' => $shopLink
                    ];
                    $shop = ShopServiceFactory::mShopService()->create($inputShop);
                }

                // Add Cart
                $cartInput = array(
                    'user_id' => (int)$decoded_token['user_id'],
                    'shop_id' => $shop['id'],
                    'status' => 1,
                );

                $cart = CartServiceFactory::mCartService()->create($cartInput);
                if (!empty($cart)) {
                    // Add CartItems
                    foreach ((array)$inputData as $item) {
                        $inputCart = (array)$item;
                        $arrRules = [
                            'amount' => 'required',
                            'domain' => 'required',
                            'image' => 'required',
                            'method' => 'required',
                            'pro_link' => 'required',
                            'rate' => 'required',
                            'site' => 'required'
                        ];
                        $arrMessages = [
                            'amount.required' => 'amount.required',
                            'domain.required' => 'domain.required',
                            'image.required' => 'image.required',
                            'method.required' => 'method.required',
                            'pro_link.required' => 'pro_link.required',
                            'rate.required' => 'rate.required',
                            'site.required' => 'site.required'
                        ];

                        $validator = Validator::make($inputCart, $arrRules, $arrMessages);
                        if ($validator->fails()) {
                            DB::rollBack();
                            return $this->sendError('Error', $validator->errors()->all());
                        }

                        $inputCart['cart_id'] = $cart['id'];
                        $inputCart['price'] = self::convertPrice($inputCart['price']);
                        $inputCart['price_arr'] = json_encode($inputCart['price_arr']);
                        CartServiceFactory::mCartService()->itemCreate($inputCart);
                    }

                    self::reUpdate($cart['id']);
                }
            }

            DB::commit();
            return $this->sendResponse($cart, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        DB::beginTransaction();
        try {
            $user = $request->user();

            // Shop
            if (empty($input['shop'])) {
                DB::rollBack();
                return $this->sendError('Error', 'Shop empty!');
            }

            $shop = $input['shop'];
            $shopNick = $shop['name'];
            $shopLink = $shop['url'];
            $shop = ShopServiceFactory::mShopService()->findByUrl($shopLink, $user->id);
            if (!$shop) {
                $inputShop = [
                    'name' => $shopNick,
                    'url' => $shopLink,
                    'user_id' => $user->id
                ];
                $shop = ShopServiceFactory::mShopService()->create($inputShop);
            }

            // Add Cart
            $cartInput = array(
                'user_id' => $user->id,
                'shop_id' => $shop['id'],
                'status' => 1,
            );

            $cart = CartServiceFactory::mCartService()->create($cartInput);
            if (!empty($cart)) {
                // cart_items
                foreach ($input['cart_items'] as $item) {
                    if (!empty($item)) {
                        if (empty($item['amount'])) {
                            $item['amount'] = 0;
                        }
                        if (empty($item['price'])) {
                            $item['price'] = 0;
                        }
                        $item['cart_id'] = $cart['id'];
                        CartServiceFactory::mCartService()->itemCreate($item);
                    }
                }
                self::reUpdate($cart['id']);
            }

            DB::commit();
            return $this->sendResponse($cart, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function json_decode_nice($json, $assoc = FALSE)
    {
        $json = str_replace(array("\n", "\r"), "", $json);
        return json_decode($json, $assoc);
    }

    public function delete($id, Request $request)
    {
        try {
            $user = $request->user();
            $cart = CartServiceFactory::mCartService()->findById($id);
            if (empty($cart)) {
                return $this->sendError('Error', ['Không tồn tại giỏ hàng!']);
            }

            if ($cart['user_id'] != $user->id) {
                return $this->sendError('Error', ['Không có quyền thay đổi giỏ hàng!']);
            }

            if ($cart['status'] == 2) {
                return $this->sendError('Error', ['Không thể thay đổi giỏ hàng!']);
            }

            CartServiceFactory::mCartService()->delete($id);
            return $this->sendResponse(true, 'Successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function itemDelete($id, Request $request)
    {
        $input = $request->all();
        DB::beginTransaction();
        try {
            $user = $request->user();
            $cartItem = CartServiceFactory::mCartService()->itemFindById($id);
            if (empty($cartItem)) {
                return $this->sendError('Error', ['Không tồn tại sản phẩm!']);
            }

            $cart = CartServiceFactory::mCartService()->findById($cartItem['cart_id']);
            if (empty($cart)) {
                return $this->sendError('Error', ['Không tồn tại giỏ hàng!']);
            }

            if ($cart['user_id'] != $user->id) {
                return $this->sendError('Error', ['Không có quyền thay đổi giỏ hàng!']);
            }

            if ($cart['status'] == 2) {
                return $this->sendError('Error', ['Không thể thay đổi giỏ hàng!']);
            }

            CartServiceFactory::mCartService()->itemDelete($id);
            self::reUpdate($cart['id']);
            DB::commit();
            return $this->sendResponse(1, 'Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
