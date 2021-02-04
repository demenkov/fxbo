For run demo:

Copy `.env` to `.env.local`

Create new database and set `DATABASE_URL` in `.env.local`

Run in terminal

> eval (ssh-agent)
> 
> make install
> 
> make build
>
> bin/console rate:import

In browser

http://localhost:8889 exchange calculator

http://localhost:8889/list rates list

For tests run

>
> make test
