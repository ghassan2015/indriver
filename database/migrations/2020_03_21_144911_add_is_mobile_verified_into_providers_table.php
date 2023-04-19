<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMobileVerifiedIntoProvidersTable extends Migration {
	
	public function up () {
		Schema::table( 'providers', function ( Blueprint $table ) {
			$table->boolean( 'is_mobile_verified' )
			      ->nullable()
			      ->default( 0 );
		} );
	}
	
	public function down () {
		Schema::table( 'providers', function ( Blueprint $table ) {
			$table->dropColumn( 'is_mobile_verified' );
		} );
	}
}
