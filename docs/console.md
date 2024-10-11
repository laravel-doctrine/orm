# Console

This package offers a bunch of artisan commands to speed up development:

Artisan command | Description
--- | --- 
`doctrine:clear:metadata:cache` | Clear all metadata cache of the various cache drivers.
`doctrine:clear:query:cache`|Clear all query cache of the various cache drivers.
`doctrine:clear:result:cache`|Clear all result cache of the various cache drivers.
`doctrine:ensure:production`|Verify that Doctrine is properly configured for a production environment.
`doctrine:generate:proxies`|Generates proxy classes for entity classes.
`doctrine:info`|Show basic information about all mapped entities.
`doctrine:schema:create`|Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.
`doctrine:schema:drop`|Drop the complete database schema of EntityManager Storage Connection or generate the corresponding SQL output.
`doctrine:schema:update`|Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata.
`doctrine:schema:validate`|Validate the mapping files.
