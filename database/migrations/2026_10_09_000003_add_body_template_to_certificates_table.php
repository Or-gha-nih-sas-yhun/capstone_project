<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Custom document wording, with {{token}} placeholders. When this
            // is filled the request prints through the general letterhead
            // layout using this text, which lets a barangay reword an
            // existing certificate or add an entirely new type without a
            // new Blade template.
            $table->text('body_template')->nullable()->after('template_file');

            // Heading above the body, e.g. "BARANGAY CERTIFICATION".
            $table->string('header_title', 150)->nullable()->after('body_template');

            // Position printed under the signature line.
            $table->string('signatory_position', 150)->nullable()->after('header_title');
        });
    }

    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['body_template', 'header_title', 'signatory_position']);
        });
    }
};
