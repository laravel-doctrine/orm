# Multiple Connections

You can use multiple Doctrine entity managers or connections. This is necessary if you are using different databases or even vendors with entirely different sets of entities. In other words, one entity manager that connects to one database will handle some entities while another entity manager that connects to another database might handle the rest.

The default manager is configured in `doctrine.managers`. This one will get used when you use the `EntityManager` directly. You can add more managers to this array.

In your application you can inject `Doctrine\Common\Persistence\ManagerRegistry`. This holds all entity managers. `ManagerRegistry@getManager()` will return the default manager. By passing through the manager name, you will get the connection you want. Alternatively you can use `getManagerForClass($entityName)` to get a manager which is suitable for that entity.
