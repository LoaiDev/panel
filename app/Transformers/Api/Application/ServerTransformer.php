<?php

namespace Pterodactyl\Transformers\Api\Application;

use Pterodactyl\Models\Server;
use Pterodactyl\Services\Acl\Api\AdminAcl;
use Pterodactyl\Transformers\Api\Transformer;
use Pterodactyl\Services\Servers\EnvironmentService;

class ServerTransformer extends Transformer
{
    protected EnvironmentService $environmentService;

    /**
     * List of resources that can be included.
     *
     * @var array
     */
    protected $availableIncludes = [
        'allocations',
        'user',
        'subusers',
        'nest',
        'egg',
        'variables',
        'location',
        'node',
        'databases',
        'transfer',
    ];

    public function handle(EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }

    public function getResourceName(): string
    {
        return Server::RESOURCE_NAME;
    }

    public function transform(Server $model): array
    {
        return [
            'id' => $model->getKey(),
            'external_id' => $model->external_id,
            'uuid' => $model->uuid,
            'identifier' => $model->uuidShort,
            'name' => $model->name,
            'description' => $model->description,
            'status' => $model->status,
            'limits' => [
                'memory' => $model->memory,
                'swap' => $model->swap,
                'disk' => $model->disk,
                'io' => $model->io,
                'cpu' => $model->cpu,
                'threads' => $model->threads,
            ],
            'feature_limits' => [
                'databases' => $model->database_limit,
                'allocations' => $model->allocation_limit,
                'backups' => $model->backup_limit,
            ],
            'owner_id' => $model->owner_id,
            'node_id' => $model->node_id,
            'allocation_id' => $model->allocation_id,
            'nest_id' => $model->nest_id,
            'egg_id' => $model->egg_id,
            'container' => [
                'startup_command' => $model->startup,
                'image' => $model->image,
                'environment' => $this->environmentService->handle($model),
            ],
            'oom_killer' => !$model->oom_disabled,
            'updated_at' => self::formatTimestamp($model->updated_at),
            'created_at' => self::formatTimestamp($model->created_at),
        ];
    }

    /**
     * Return a generic array of allocations for this server.
     *
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeAllocations(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_ALLOCATIONS)) {
            return $this->null();
        }

        return $this->collection($server->allocations, new AllocationTransformer());
    }

    /**
     * Return a generic array of data about subusers for this server.
     *
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeSubusers(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_USERS)) {
            return $this->null();
        }

        return $this->collection($server->subusers, new SubuserTransformer());
    }

    /**
     * Return a generic array of data about subusers for this server.
     *
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeUser(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_USERS)) {
            return $this->null();
        }

        return $this->item($server->user, new UserTransformer());
    }

    /**
     * Return a generic array with nest information for this server.
     *
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeNest(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_NESTS)) {
            return $this->null();
        }

        return $this->item($server->nest, new NestTransformer());
    }

    /**
     * Return a generic array with egg information for this server.
     *
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeEgg(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_EGGS)) {
            return $this->null();
        }

        return $this->item($server->egg, new EggTransformer());
    }

    /**
     * Return a generic array of data about subusers for this server.
     *
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeVariables(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVERS)) {
            return $this->null();
        }

        return $this->collection($server->variables, new ServerVariableTransformer());
    }

    /**
     * Return a generic array with location information for this server.
     *
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeLocation(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_LOCATIONS)) {
            return $this->null();
        }

        return $this->item($server->location, new LocationTransformer());
    }

    /**
     * Return a generic array with node information for this server.
     *
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeNode(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_NODES)) {
            return $this->null();
        }

        return $this->item($server->node, new NodeTransformer());
    }

    /**
     * Return a generic array with database information for this server.
     *
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeDatabases(Server $server)
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVER_DATABASES)) {
            return $this->null();
        }

        return $this->collection($server->databases, new ServerDatabaseTransformer());
    }
}
