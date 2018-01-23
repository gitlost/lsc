<?php

class LSC_DB {
	static $dbhost = '127.0.0.1';
	static $dbname = 'lsc';
	static $dbuser = 'root';
	static $dbpass = 'mb';

	static $dbh = null;
	static $db_selected = false; // Whether database selected (opened).

	static function create_db() {
		$sql = "CREATE DATABASE IF NOT EXISTS `" . self::$dbname . "` CHARSET 'UTF8'";
		return self::query( $sql, true /*no_select*/ );
	}

	static function create_members_table() {
		$sql = "CREATE TABLE IF NOT EXISTS members (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL DEFAULT '',
			address VARCHAR(255) NOT NULL DEFAULT '',
			address2 VARCHAR(255) NOT NULL DEFAULT '',
			phone VARCHAR(255) NOT NULL DEFAULT '',
			email VARCHAR(255) NOT NULL DEFAULT '',
			gender ENUM('M', 'F') NOT NULL DEFAULT 'M',
			is_exec TINYINT(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (id)
		)";
		return self::query( $sql );
	}

	static function create_member( $name, $address = '', $address2 = '', $phone = '', $email = '', $gender = 'M', $is_exec = 0 ) {
		$sql = sprintf( "INSERT INTO members (name, address, address2, phone, email, gender, is_exec) VALUES('%s', '%s', '%s', '%s', '%s', '%s', %s)",
			mysqli_escape_string( self::$dbh, $name ),
			mysqli_escape_string( self::$dbh, $address ),
			mysqli_escape_string( self::$dbh, $address2 ),
			mysqli_escape_string( self::$dbh, $phone ),
			mysqli_escape_string( self::$dbh, $email ),
			$gender === 'F' ? 'F' : 'M',
			$is_exec ? '1' : '0'
		);
		return self::query( $sql );
	}

	static function list_members() {
		$ret = array();

		$sql = "SELECT * FROM members";
		$rows = self::query( $sql );
		foreach ( $rows as $row ) {
			$ret[] = $row;
		}
		return $ret;
	}

	static function delete_member( $id ) {
		$sql = sprintf( "DELETE FROM members WHERE id = %d", $id );
		return self::query( $sql );
	}

	static function query( $sql_cmd, $no_select = false ) {
		$ret = null;
		if ( self::$dbh === null ) {
			if ( ! ( self::$dbh = mysqli_connect( self::$dbhost, self::$dbuser, self::$dbpass ) ) ) {
				throw new \RuntimeException(
					sprintf( "Failed to connect to MySQL database host '%s', user '%s', pass '%s'.", self::$dbhost, self::$dbuser, self::$dbpass )
				);
			}
		}
		if ( ! $no_select && ! self::$db_selected ) {
			if ( ! mysqli_select_db( self::$dbh, self::$dbname ) ) {
				throw new \RuntimeException( sprintf( "Failed to open MySQL database name '%s'.", self::$dbname ) );
			}
			self::$db_selected = true;
		}
		if ( ! ( $ret = mysqli_query( self::$dbh, $sql_cmd ) ) ) {
			throw new \RuntimeException( sprintf( "Failed to execute MySQL query '%s': %d: %s", $sql_cmd, mysqli_errno( self::$dbh ), mysqli_error( self::$dbh ) ) );
		}
		return $ret;
	}
}
