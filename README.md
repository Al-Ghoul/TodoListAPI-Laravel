# Todo List API

TodoList API's a RESTful API that allows users to manage their to-do list. 

Project URL: https://roadmap.sh/projects/todo-list-api


## Development \[Nix\]
This project is built with [Laravel 11.x](https://laravel.com/docs/11.x).

```bash
# Clone & cd into project
# Enter development shell
nix develop

# Run dev server 
cd app && php artisan serve
```

## Routes

|             URI        | Description                                            |
| :-------------------- | :-----------------------------------------------------: |
| /api/auth/register    |    Register a new user.                                 |
| /api/auth/login       |    Login a user.                                        |
| /api/auth/logout      |    Logout a user.                                       |
| /api/auth/refresh     |    Refresh a token.                                     |
| /api/auth/profile     |    Get authenticated user's data.                       |
| /api/todos            |    Get all todos. paginated with limit (limit is 15 by default) and page.  |
| /api/todos            |    Create a new todo.                                   |
| /api/todos/{id}       |    Update a todo.                                       |
| /api/todos/{id}       |    Delete a todo.                                       |

## Technology stack

- [Laravel](https://laravel.com/)
- [Nix](https://nixos.org/nix/)


