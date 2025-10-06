## Custom Bus Adapter

If you're using a different command bus, implement the `CommandBus`:
```php
use YourVendor\DddToolkit\Bus\CommandBus;

#[AutoconfigureTag('app.my_bus_adapter')]
class MyBusAdapter implements CommandBus
{
    public function dispatch(Command $command): void
    {
        // Forward to your bus implementation
        $this->myBus->dispatch($command);
    }
}
```

Configure it:
```yaml
ddd_toolkit:
  buses:
    command_bus: 'app.my_bus_adapter'
```

---
///
We do not support returning a value from a command bus.
We guide you to follow the [CQRS](https://martinfowler.com/bliki/CQRS.html) pattern.
If needed: Returning a value from the command bus is possible by using some kind of stateful global context service. 
Then reading its state after the command execution.
///
---