<?php 

namespace app\models;

class UserModel {

    public function getUsers(){
        $users = [
			[ 'id' => 1, 'name' => 'Bob Jones', 'email' => 'bob@example.com' ],
			[ 'id' => 2, 'name' => 'Bob Smith', 'email' => 'bsmith@example.com' ],
			[ 'id' => 3, 'name' => 'Suzy Johnson', 'email' => 'suzy@example.com' ],
			[ 'id' => 4, 'name' => 'sc John', 'email' => 'sc@gmail.com' ],
            [ 'id' => 5, 'name' => 'zzzz', 'email' => 'z@gmail.com' ],
		];
        return $users;
    }
    public function getUser($id){
        $users = [
			[ 'id' => 1, 'name' => 'Bob Jones', 'email' => 'bob@example.com' ],
			[ 'id' => 2, 'name' => 'Bob Smith', 'email' => 'bsmith@example.com' ],
			[ 'id' => 3, 'name' => 'Suzy Johnson', 'email' => 'suzy@example.com' ],
			[ 'id' => 4, 'name' => 'sc John', 'email' => 'sc@gmail.com' ],
            [ 'id' => 5, 'name' => 'zzzz', 'email' => 'z@gmail.com' ],
		];
        return $users[$id];
    }
}

?>