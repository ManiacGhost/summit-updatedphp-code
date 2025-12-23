#!/bin/bash

# Redis Cart API Test Commands
# Base URL: http://127.0.0.1:8000/api/cart/redis

echo "=== 1. GET CART (Empty) ==="
curl -X GET http://127.0.0.1:8000/api/cart/redis \
  -H 'Accept: application/json'

echo -e "\n\n=== 2. ADD ITEM 1 (Product Variant 5, Qty 2, Price 999.99) ==="
curl -X POST http://127.0.0.1:8000/api/cart/redis/add \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "product_variant_id": 5,
    "quantity": 2,
    "price": 999.99
  }'

echo -e "\n\n=== 3. ADD ITEM 2 (Product Variant 7, Qty 1, Price 1499.50) ==="
curl -X POST http://127.0.0.1:8000/api/cart/redis/add \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "product_variant_id": 7,
    "quantity": 1,
    "price": 1499.50
  }'

echo -e "\n\n=== 4. GET CART (With Items) ==="
curl -X GET http://127.0.0.1:8000/api/cart/redis \
  -H 'Accept: application/json'

echo -e "\n\n=== 5. INCREASE QUANTITY (Variant 5, Increase by 1) ==="
curl -X POST http://127.0.0.1:8000/api/cart/redis/increase/5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "amount": 1
  }'

echo -e "\n\n=== 6. REDUCE QUANTITY (Variant 7, Reduce by 1 - should remove) ==="
curl -X POST http://127.0.0.1:8000/api/cart/redis/reduce/7 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "amount": 1
  }'

echo -e "\n\n=== 7. UPDATE QUANTITY (Variant 5, Set to 5) ==="
curl -X POST http://127.0.0.1:8000/api/cart/redis/update/5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "quantity": 5
  }'

echo -e "\n\n=== 8. GET CART SIZE ==="
curl -X GET http://127.0.0.1:8000/api/cart/redis/size \
  -H 'Accept: application/json'

echo -e "\n\n=== 9. REMOVE ITEM (Variant 5) ==="
curl -X DELETE http://127.0.0.1:8000/api/cart/redis/remove/5 \
  -H 'Accept: application/json'

echo -e "\n\n=== 10. CLEAR CART ==="
curl -X POST http://127.0.0.1:8000/api/cart/redis/clear \
  -H 'Accept: application/json'

echo -e "\n\n=== 11. GET FINAL CART (Should be empty) ==="
curl -X GET http://127.0.0.1:8000/api/cart/redis \
  -H 'Accept: application/json'

echo -e "\n\nTest completed!\n"
