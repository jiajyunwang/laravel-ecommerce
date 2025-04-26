# Laravel E-commerce

這是一個使用 Laravel 所建立的一個電商網站。

## Demo

前台: https://laravel-ecommerce.zeabur.app/
```
Email: user1@example.com
Password: useruser1
```
```
Email: user2@example.com
Password: useruser2
```

後台: https://laravel-ecommerce.zeabur.app/

```
Email: admin@example.com
Password: admin123
```

## 網站功能

### 前台

* 全站<BR>
⌞商品搜尋(Elasticsearch)<BR>
⌞客服即時通訊(Redis + socket.io)<BR>

* 首頁<BR>
⌞商品陳列<BR>
⌞商品排序<BR>

* 商品<BR>
⌞商品介紹<BR>
⌞商品數量<BR>
⌞直接購買<BR>
⌞加入購物車<BR>
⌞評價列表<BR>
⌞評價排序<BR>

* 購物車<BR>
⌞購物車商品列表<BR>
⌞選取商品<BR>
⌞移除商品<BR>
⌞商品數量<BR>

* 結帳<BR>
⌞帳單<BR>
⌞信用卡付款(Stripe API)<BR>

* 訂單<BR>
⌞訂單列表<BR>
⌞訂單操作(取消、完成、評價、重新購買)<BR>

* 用戶<BR>
⌞註冊<BR>
⌞登入<BR>
⌞基本資料修改<BR>

### 後台

* 後台<BR>
⌞商品管理<BR>
⌞訂單管理<BR>

## 使用技術

1. PHP
2. Laravel
3. JS
4. jQuery
5. MySQL
6. AJAX
7. Fetch 
8. Elasticsearch
9. Laravel Broadcasting(Redis + socket.io)
10. Stripe API
11. Infinite Scroll
12. Laravel Dompdf
13. Intervention Image

## 注意事項

信用卡付款為測試模式，請使用以下測試用卡號

| 卡號 | 月/年 | CVC  |
| -------- | -------- | -------- |
| 4242 4242 4242 4242 | any | any |