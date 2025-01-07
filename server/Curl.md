curl -X GET "http://localhost/api/getMetodes/getPosts.php?page=1&search=post&limit=5"

curl -X POST \
 -F "title=My First Post" \
 -F "content=This is the content of my first post." \
http://localhost/api/postMetodes/addPost.php -H "Authorization: Bearer password"

curl -X DELETE \
 -H "Content-Type: application/json" \
 -d '{
"id": 1
}' \
http://localhost/api/deleteMetodes/deletePost.php -H "Authorization: Bearer password"

curl -X PUT \
 -H "Content-Type: application/json" \
 -d '{
"id": 1,
"title": "Updated Post Title",
"content": "This is the updated content of the post."
}' \
http://localhost/api/putMetodes/updatePost.php -H "Authorization: Bearer password"

curl -X POST \
 -H "Content-Type: application/json" \
 -d '{
"id": 1
}' \
http://localhost/api/postMetodes/getPost.php -H "Authorization: Bearer password"
