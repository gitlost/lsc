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
			primary_section_id BIGINT(20) UNSIGNED,
			PRIMARY KEY (id)
		)";
		self::query( $sql );
		self::add_col_if_not_exists( 'members', 'primary_section_id', 'BIGINT(20) UNSIGNED' );
	}

	static function create_member( $name, $address = '', $address2 = '', $phone = '', $email = '', $gender = 'M', $primary_section_id = '', $other_section_ids = '' ) {
		$sql = sprintf( "INSERT INTO members (name, address, address2, phone, email, gender, primary_section_id) VALUES('%s', '%s', '%s', '%s', '%s', '%s', %d)",
			mysqli_escape_string( self::$dbh, $name ),
			mysqli_escape_string( self::$dbh, $address ),
			mysqli_escape_string( self::$dbh, $address2 ),
			mysqli_escape_string( self::$dbh, $phone ),
			mysqli_escape_string( self::$dbh, $email ),
			$gender === 'F' ? 'F' : 'M',
			(int) $primary_section_id
		);
		self::query( $sql );
		$id = mysqli_insert_id( self::$dbh );
		self::create_member_sections( $id, $primary_section_id, $other_section_ids );
	}

	static function update_member( $id, $name, $address = '', $address2 = '', $phone = '', $email = '', $gender = 'M', $primary_section_id = '', $other_section_ids = '' ) {
		$sql = sprintf( "UPDATE members SET name = '%s', address = '%s', address2 = '%s', phone = '%s', email = '%s', gender = '%s', primary_section_id = %d WHERE id = %d",
			mysqli_escape_string( self::$dbh, $name ),
			mysqli_escape_string( self::$dbh, $address ),
			mysqli_escape_string( self::$dbh, $address2 ),
			mysqli_escape_string( self::$dbh, $phone ),
			mysqli_escape_string( self::$dbh, $email ),
			$gender === 'F' ? 'F' : 'M',
			(int) $primary_section_id,
			(int) $id
		);
		self::query( $sql );
		self::delete_member_sections( $id );
		self::create_member_sections( $id, $primary_section_id, $other_section_ids );
	}

	static function get_member( $id ) {
		$sql = sprintf( "SELECT name, address, address2, phone, email, gender, primary_section_id FROM members WHERE members.id = %d", $id );
		$row = self::query( $sql );
		return mysqli_fetch_row( $row );
	}

	static function list_members() {
		$ret = array();

		$sql = "SELECT members.id, members.name, members.address, members.address2, members.phone, members.email, members.gender, sections.name AS primary_section_name FROM members"
				. " LEFT JOIN sections ON sections.id = members.primary_section_id ORDER BY members.name";
		$rows = self::query( $sql );
		foreach ( $rows as $row ) {
			$ret[] = $row;
		}
		return $ret;
	}

	static function delete_member( $id ) {
		$sql = sprintf( "DELETE FROM members WHERE id = %d", $id );
		self::query( $sql );
		$sql = sprintf( "UPDATE sections SET admin_member_id = 0 WHERE admin_member_id = %d", $id );
		self::query( $sql );
	}

	static function create_sections_table() {
		$sql = "CREATE TABLE IF NOT EXISTS sections (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL DEFAULT '',
			admin_member_id BIGINT(20) UNSIGNED,
			PRIMARY KEY (id)
		)";
		return self::query( $sql );
	}

	static function create_section( $name, $admin_member_id = 0 ) {
		$sql = sprintf( "INSERT INTO sections (name, admin_member_id) VALUES('%s', %d)",
			mysqli_escape_string( self::$dbh, $name ),
			(int) $admin_member_id
		);
		return self::query( $sql );
	}

	static function update_section( $id, $name, $admin_member_id = 0 ) {
		$sql = sprintf( "UPDATE sections SET name = '%s', admin_member_id = %d WHERE id = %d",
			mysqli_escape_string( self::$dbh, $name ),
			(int) $admin_member_id,
			(int) $id
		);
		return self::query( $sql );
	}

	static function get_section( $id ) {
		$sql = sprintf( "SELECT id, name, admin_member_id FROM sections WHERE id = %d", $id );
		$row = self::query( $sql );
		return mysqli_fetch_row( $row );
	}

	static function list_sections() {
		$ret = array();

		$sql = "SELECT sections.id, sections.name, sections.admin_member_id, members.name AS admin_member_name FROM sections"
				. " LEFT JOIN members ON sections.admin_member_id = members.id ORDER BY sections.id";
		$rows = self::query( $sql );
		foreach ( $rows as $row ) {
			$ret[] = $row;
		}
		return $ret;
	}

	static function delete_section( $id ) {
		$sql = sprintf( "DELETE FROM sections WHERE id = %d", $id );
		self::query( $sql );
		$sql = sprintf( "UPDATE members SET primary_section_id = 0 WHERE primary_section_id = %d", $id );
		self::query( $sql );
		self::delete_member_sections( $id );
	}

	static function create_members_sections_table() {
		$sql = "CREATE TABLE IF NOT EXISTS members_sections (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			member_id BIGINT(20) UNSIGNED,
			section_id BIGINT(20) UNSIGNED,
			PRIMARY KEY (id)
		)";
		return self::query( $sql );
	}

	static function create_member_section( $member_id, $section_id ) {
		$sql = sprintf( "INSERT INTO members_sections (member_id, section_id) VALUES(%d, %d)",
			(int) $member_id,
			(int) $section_id
		);
		return self::query( $sql );
	}

	static function create_member_sections( $member_id, $primary_section_id, $section_ids = array() ) {
		if ( $section_ids ) {
			$section_ids = array_values( array_unique( array_filter( $section_ids ) ) );
			foreach ( $section_ids as $section_id ) {
				if ( $section_id !== $primary_section_id ) {
					self::create_member_section( $member_id, $section_id );
				}
			}
		}
	}

	static function get_member_sections( $member_id ) {
		$ret = array();

		$sql = sprintf(
			"SELECT sections.id FROM members_sections"
			. " LEFT JOIN sections ON members_sections.section_id = sections.id WHERE members_sections.member_id = %d", $member_id
		);
		$rows = self::query( $sql );
		foreach ( $rows as $row ) {
			$ret[] = $row['id'];
		}
		return $ret;
	}

	static function delete_member_sections( $member_id ) {
		$sql = sprintf( "DELETE FROM members_sections WHERE member_id = %d", $member_id );
		return self::query( $sql );
	}

	static function add_col_if_not_exists( $table, $col, $col_def ) {
		$sql = "SHOW COLUMNS IN $table LIKE '$col'";
		$row = self::query( $sql );
		if ( mysqli_num_rows( $row ) ) {
			return 0;
		}
		$sql = "ALTER TABLE $table ADD $col $col_def";
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
