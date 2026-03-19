<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute les index critiques pour supporter 1000+ utilisateurs simultanés
     */
    public function up(): void
    {
        // Index pour les demandes (recherches fréquentes)
        Schema::table('demandes', function (Blueprint $table) {
            if (!$this->indexExists('demandes', 'idx_demandes_statut')) {
                $table->index('statut', 'idx_demandes_statut');
            }
            if (!$this->indexExists('demandes', 'idx_demandes_created_at')) {
                $table->index('created_at', 'idx_demandes_created_at');
            }
            if (!$this->indexExists('demandes', 'idx_demandes_code_suivi')) {
                $table->index('code_suivi', 'idx_demandes_code_suivi');
            }
            if (!$this->indexExists('demandes', 'idx_demandes_type_statut')) {
                $table->index(['type_demande', 'statut'], 'idx_demandes_type_statut');
            }
        });

        // Index pour public_requests (si utilisé)
        if (Schema::hasTable('public_requests')) {
            Schema::table('public_requests', function (Blueprint $table) {
                if (Schema::hasColumn('public_requests', 'statut') && !$this->indexExists('public_requests', 'idx_public_requests_statut')) {
                    $table->index('statut', 'idx_public_requests_statut');
                }
                if (Schema::hasColumn('public_requests', 'created_at') && !$this->indexExists('public_requests', 'idx_public_requests_created_at')) {
                    $table->index('created_at', 'idx_public_requests_created_at');
                }
            });
        }

        // Index pour les stocks (requêtes temps réel)
        if (Schema::hasTable('stocks')) {
            Schema::table('stocks', function (Blueprint $table) {
                if (!$this->indexExists('stocks', 'idx_stocks_warehouse_id')) {
                    $table->index('warehouse_id', 'idx_stocks_warehouse_id');
                }
                if (!$this->indexExists('stocks', 'idx_stocks_stock_type_id')) {
                    $table->index('stock_type_id', 'idx_stocks_stock_type_id');
                }
            });
        }

        // Index pour les mouvements de stock
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (!$this->indexExists('stock_movements', 'idx_stock_movements_stock_id')) {
                    $table->index('stock_id', 'idx_stock_movements_stock_id');
                }
                if (!$this->indexExists('stock_movements', 'idx_stock_movements_created_at')) {
                    $table->index('created_at', 'idx_stock_movements_created_at');
                }
                if (!$this->indexExists('stock_movements', 'idx_stock_movements_type')) {
                    $table->index('type', 'idx_stock_movements_type');
                }
            });
        }

        // Index pour les actualités (site public)
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table) {
                if (!$this->indexExists('news', 'idx_news_is_published')) {
                    $table->index('is_published', 'idx_news_is_published');
                }
                if (!$this->indexExists('news', 'idx_news_published_at')) {
                    $table->index('published_at', 'idx_news_published_at');
                }
                if (!$this->indexExists('news', 'idx_news_published_combo')) {
                    $table->index(['is_published', 'published_at'], 'idx_news_published_combo');
                }
            });
        }

        // Index pour les entrepôts
        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                if (Schema::hasColumn('warehouses', 'region') && !$this->indexExists('warehouses', 'idx_warehouses_region')) {
                    $table->index('region', 'idx_warehouses_region');
                }
                if (Schema::hasColumn('warehouses', 'statut') && !$this->indexExists('warehouses', 'idx_warehouses_statut')) {
                    $table->index('statut', 'idx_warehouses_statut');
                }
            });
        }

        // Index pour le personnel
        if (Schema::hasTable('personnel')) {
            Schema::table('personnel', function (Blueprint $table) {
                if (Schema::hasColumn('personnel', 'warehouse_id') && !$this->indexExists('personnel', 'idx_personnel_warehouse_id')) {
                    $table->index('warehouse_id', 'idx_personnel_warehouse_id');
                }
                if (Schema::hasColumn('personnel', 'statut') && !$this->indexExists('personnel', 'idx_personnel_statut')) {
                    $table->index('statut', 'idx_personnel_statut');
                }
                if (Schema::hasColumn('personnel', 'poste') && !$this->indexExists('personnel', 'idx_personnel_poste')) {
                    $table->index('poste', 'idx_personnel_poste');
                }
            });
        }

        // Index pour les utilisateurs
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'idx_users_role')) {
                $table->index('role', 'idx_users_role');
            }
            if (!$this->indexExists('users', 'idx_users_is_active')) {
                $table->index('is_active', 'idx_users_is_active');
            }
        });

        // Index pour les rapports SIM
        if (Schema::hasTable('sim_reports')) {
            Schema::table('sim_reports', function (Blueprint $table) {
                if (!$this->indexExists('sim_reports', 'idx_sim_reports_is_published')) {
                    $table->index('is_published', 'idx_sim_reports_is_published');
                }
                if (!$this->indexExists('sim_reports', 'idx_sim_reports_created_at')) {
                    $table->index('created_at', 'idx_sim_reports_created_at');
                }
            });
        }

        // Index pour les collectes SIM (si table existe)
        if (Schema::hasTable('sim_collections')) {
            Schema::table('sim_collections', function (Blueprint $table) {
                if (Schema::hasColumn('sim_collections', 'sim_market_id') && !$this->indexExists('sim_collections', 'idx_sim_collections_market_id')) {
                    $table->index('sim_market_id', 'idx_sim_collections_market_id');
                }
                if (Schema::hasColumn('sim_collections', 'collector_id') && !$this->indexExists('sim_collections', 'idx_sim_collections_collector_id')) {
                    $table->index('collector_id', 'idx_sim_collections_collector_id');
                }
                if (Schema::hasColumn('sim_collections', 'status') && !$this->indexExists('sim_collections', 'idx_sim_collections_status')) {
                    $table->index('status', 'idx_sim_collections_status');
                }
                if (Schema::hasColumn('sim_collections', 'collection_date') && !$this->indexExists('sim_collections', 'idx_sim_collections_date')) {
                    $table->index('collection_date', 'idx_sim_collections_date');
                }
            });
        }

        // Index pour les messages
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (Schema::hasColumn('messages', 'read_at') && !$this->indexExists('messages', 'idx_messages_read_at')) {
                    $table->index('read_at', 'idx_messages_read_at');
                }
                if (Schema::hasColumn('messages', 'created_at') && !$this->indexExists('messages', 'idx_messages_created_at')) {
                    $table->index('created_at', 'idx_messages_created_at');
                }
            });
        }

        // Index pour les notifications
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'user_id') && !$this->indexExists('notifications', 'idx_notifications_user_id')) {
                    $table->index('user_id', 'idx_notifications_user_id');
                }
                if (Schema::hasColumn('notifications', 'read_at') && !$this->indexExists('notifications', 'idx_notifications_read_at')) {
                    $table->index('read_at', 'idx_notifications_read_at');
                }
            });
        }

        // Index pour les sessions
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                if (Schema::hasColumn('sessions', 'user_id') && !$this->indexExists('sessions', 'idx_sessions_user_id')) {
                    $table->index('user_id', 'idx_sessions_user_id');
                }
                if (Schema::hasColumn('sessions', 'last_activity') && !$this->indexExists('sessions', 'idx_sessions_last_activity')) {
                    $table->index('last_activity', 'idx_sessions_last_activity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression des index dans l'ordre inverse
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropIndex('idx_sessions_user_id');
                $table->dropIndex('idx_sessions_last_activity');
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('idx_notifications_user_id');
                $table->dropIndex('idx_notifications_read_at');
            });
        }

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex('idx_messages_read_at');
                $table->dropIndex('idx_messages_created_at');
            });
        }

        if (Schema::hasTable('sim_collections')) {
            Schema::table('sim_collections', function (Blueprint $table) {
                $table->dropIndex('idx_sim_collections_market_id');
                $table->dropIndex('idx_sim_collections_collector_id');
                $table->dropIndex('idx_sim_collections_status');
                $table->dropIndex('idx_sim_collections_date');
            });
        }

        if (Schema::hasTable('sim_reports')) {
            Schema::table('sim_reports', function (Blueprint $table) {
                $table->dropIndex('idx_sim_reports_is_published');
                $table->dropIndex('idx_sim_reports_created_at');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_is_active');
        });

        if (Schema::hasTable('personnel')) {
            Schema::table('personnel', function (Blueprint $table) {
                $table->dropIndex('idx_personnel_warehouse_id');
                $table->dropIndex('idx_personnel_statut');
                $table->dropIndex('idx_personnel_poste');
            });
        }

        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropIndex('idx_warehouses_region');
                $table->dropIndex('idx_warehouses_statut');
            });
        }

        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropIndex('idx_news_is_published');
                $table->dropIndex('idx_news_published_at');
                $table->dropIndex('idx_news_published_combo');
            });
        }

        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropIndex('idx_stock_movements_stock_id');
                $table->dropIndex('idx_stock_movements_created_at');
                $table->dropIndex('idx_stock_movements_type');
            });
        }

        if (Schema::hasTable('stocks')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropIndex('idx_stocks_warehouse_id');
                $table->dropIndex('idx_stocks_stock_type_id');
            });
        }

        if (Schema::hasTable('public_requests')) {
            Schema::table('public_requests', function (Blueprint $table) {
                $table->dropIndex('idx_public_requests_statut');
                $table->dropIndex('idx_public_requests_created_at');
            });
        }

        Schema::table('demandes', function (Blueprint $table) {
            $table->dropIndex('idx_demandes_statut');
            $table->dropIndex('idx_demandes_created_at');
            $table->dropIndex('idx_demandes_code_suivi');
            $table->dropIndex('idx_demandes_type_statut');
        });
    }

    /**
     * Vérifie si un index existe
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $connection = Schema::getConnection();
            $databaseName = $connection->getDatabaseName();
            
            $indexes = $connection->select(
                "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?",
                [$databaseName, $table, $index]
            );
            
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};
