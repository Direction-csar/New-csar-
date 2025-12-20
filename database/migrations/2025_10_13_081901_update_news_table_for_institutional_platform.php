<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Supprimer les anciennes colonnes si elles existent
            if (Schema::hasColumn('news', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('news', 'video_url')) {
                $table->dropColumn('video_url');
            }
            if (Schema::hasColumn('news', 'document')) {
                $table->dropColumn('document');
            }
            if (Schema::hasColumn('news', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('news', function (Blueprint $table) {
            // Ajouter les nouvelles colonnes pour une plateforme institutionnelle complète
            
            // Informations de base
            if (!Schema::hasColumn('news', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (!Schema::hasColumn('news', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('news', 'category')) {
                $table->string('category')->default('actualite')->after('excerpt');
            }
            if (!Schema::hasColumn('news', 'status')) {
                $table->enum('status', ['draft', 'published', 'pending'])->default('draft')->after('category');
            }
            
            // Médias et documents
            if (!Schema::hasColumn('news', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('content');
            }
            if (!Schema::hasColumn('news', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('news', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('cover_image');
            }
            if (!Schema::hasColumn('news', 'document_file')) {
                $table->string('document_file')->nullable()->after('youtube_url');
            }
            if (!Schema::hasColumn('news', 'document_title')) {
                $table->string('document_title')->nullable()->after('document_file');
            }
            
            // Options de publication
            if (!Schema::hasColumn('news', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('document_title');
            }
            if (!Schema::hasColumn('news', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('is_featured');
            }
            
            // Métadonnées et SEO
            if (!Schema::hasColumn('news', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('is_public');
            }
            if (!Schema::hasColumn('news', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('news', 'tags')) {
                $table->json('tags')->nullable()->after('meta_description');
            }
            
            // Statistiques et tracking
            if (!Schema::hasColumn('news', 'views_count')) {
                $table->integer('views_count')->default(0)->after('tags');
            }
            if (!Schema::hasColumn('news', 'downloads_count')) {
                $table->integer('downloads_count')->default(0)->after('views_count');
            }
            
            // Auteur et dates
            if (!Schema::hasColumn('news', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('downloads_count');
            }
            if (!Schema::hasColumn('news', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('news', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('published_at');
            }
        });

        // Ajouter les index pour optimiser les performances - skip if they exist
        try {
            Schema::table('news', function (Blueprint $table) {
                $table->index(['status', 'is_published']);
            });
        } catch (\Exception $e) {
            // Index already exists
        }
        
        try {
            Schema::table('news', function (Blueprint $table) {
                $table->index(['category', 'is_published']);
            });
        } catch (\Exception $e) {
            // Index already exists
        }
        
        try {
            Schema::table('news', function (Blueprint $table) {
                $table->index(['is_featured', 'is_published']);
            });
        } catch (\Exception $e) {
            // Index already exists
        }
        
        try {
            Schema::table('news', function (Blueprint $table) {
                $table->index(['published_at']);
            });
        } catch (\Exception $e) {
            // Index already exists
        }
        
        try {
            Schema::table('news', function (Blueprint $table) {
                $table->index(['created_by']);
            });
        } catch (\Exception $e) {
            // Index already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Supprimer les index
            $table->dropIndex(['status', 'is_published']);
            $table->dropIndex(['category', 'is_published']);
            $table->dropIndex(['is_featured', 'is_published']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['created_by']);
            
            // Supprimer les nouvelles colonnes
            $table->dropColumn([
                'slug',
                'excerpt',
                'category',
                'status',
                'featured_image',
                'cover_image',
                'youtube_url',
                'document_file',
                'document_title',
                'is_featured',
                'is_public',
                'meta_title',
                'meta_description',
                'tags',
                'views_count',
                'downloads_count',
                'created_by',
                'updated_by',
                'scheduled_at'
            ]);
        });

        // Restaurer les anciennes colonnes
        Schema::table('news', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('document')->nullable();
            $table->string('type')->default('news');
        });
    }
};