<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'active_storage')) {
                $table->text('active_storage')->nullable();
            }
            if (!Schema::hasColumn('settings', 'keep_local_copy')) {
                $table->tinyInteger('keep_local_copy')->default(0);
            }

            // FTP
            if (!Schema::hasColumn('settings', 'ftp_storage')) {
                $table->tinyInteger('ftp_storage')->default(0);
            }
            if (!Schema::hasColumn('settings', 'ftp_host')) {
                $table->text('ftp_host')->nullable();
            }
            if (!Schema::hasColumn('settings', 'ftp_port')) {
                $table->integer('ftp_port')->default(21)->nullable();
            }
            if (!Schema::hasColumn('settings', 'ftp_username')) {
                $table->text('ftp_username')->nullable();
            }
            if (!Schema::hasColumn('settings', 'ftp_password')) {
                $table->text('ftp_password')->nullable();
            }
            if (!Schema::hasColumn('settings', 'ftp_path')) {
                $table->text('ftp_path')->nullable();
            }
            if (!Schema::hasColumn('settings', 'ftp_url')) {
                $table->text('ftp_url')->nullable();
            }
            if (!Schema::hasColumn('settings', 'ftp_pasv')) {
                $table->tinyInteger('ftp_pasv')->default(1);
            }
            if (!Schema::hasColumn('settings', 'ftp_ssl')) {
                $table->tinyInteger('ftp_ssl')->default(0);
            }

            // WebDAV
            if (!Schema::hasColumn('settings', 'webdav_storage')) {
                $table->tinyInteger('webdav_storage')->default(0);
            }
            if (!Schema::hasColumn('settings', 'webdav_url')) {
                $table->text('webdav_url')->nullable();
            }
            if (!Schema::hasColumn('settings', 'webdav_username')) {
                $table->text('webdav_username')->nullable();
            }
            if (!Schema::hasColumn('settings', 'webdav_password')) {
                $table->text('webdav_password')->nullable();
            }
            if (!Schema::hasColumn('settings', 'webdav_path')) {
                $table->text('webdav_path')->nullable();
            }
            if (!Schema::hasColumn('settings', 'webdav_public_url')) {
                $table->text('webdav_public_url')->nullable();
            }

            // SFTP / SCP / RSync
            if (!Schema::hasColumn('settings', 'sftp_storage')) {
                $table->tinyInteger('sftp_storage')->default(0);
            }
            if (!Schema::hasColumn('settings', 'sftp_host')) {
                $table->text('sftp_host')->nullable();
            }
            if (!Schema::hasColumn('settings', 'sftp_port')) {
                $table->integer('sftp_port')->default(22)->nullable();
            }
            if (!Schema::hasColumn('settings', 'sftp_username')) {
                $table->text('sftp_username')->nullable();
            }
            if (!Schema::hasColumn('settings', 'sftp_password')) {
                $table->text('sftp_password')->nullable();
            }
            if (!Schema::hasColumn('settings', 'sftp_key')) {
                $table->text('sftp_key')->nullable();
            }
            if (!Schema::hasColumn('settings', 'sftp_path')) {
                $table->text('sftp_path')->nullable();
            }
            if (!Schema::hasColumn('settings', 'sftp_url')) {
                $table->text('sftp_url')->nullable();
            }
            if (!Schema::hasColumn('settings', 'sftp_method')) {
                $table->text('sftp_method')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'active_storage', 'keep_local_copy',
                'ftp_storage', 'ftp_host', 'ftp_port', 'ftp_username', 'ftp_password', 'ftp_path', 'ftp_url', 'ftp_pasv', 'ftp_ssl',
                'webdav_storage', 'webdav_url', 'webdav_username', 'webdav_password', 'webdav_path', 'webdav_public_url',
                'sftp_storage', 'sftp_host', 'sftp_port', 'sftp_username', 'sftp_password', 'sftp_key', 'sftp_path', 'sftp_url', 'sftp_method'
            ]);
        });
    }
};
