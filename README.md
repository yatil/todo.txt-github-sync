# todo.txt-github-sync

This is the first implementation of a **one-way** sync between github issues and your todo.txt file.

## Configuration

1. Clone this repository and symlink github and github.php in your `~/.todo.actions.d/` directory (by using `ln -s path/to/repo/github ~/.todo.actions.d/github` and `ln -s path/to/repo/github.php ~/.todo.actions.d/github.php`)
2. Edit `github.php` in the repository and **change the access token** in the line `define("TOKEN", "");` to a token for your account. [See here how to get your token.](https://help.github.com/articles/creating-an-access-token-for-command-line-use/)
3. _Optional:_ If you want to only sync issues that a specific user (you?) is assigned to, change `define("USERNAME", "");` and add your username. For me that would be `define("USERNAME", "yatil");`
4. Add a `github.txt` file to the directory where your `todo.txt` file lives.
5. Add the repositories that should be synced to that file: One repository per line in the following format: `owner/repositoryname`. For this repository it would be `yatil/todo.txt-github-sync`.
6. Run `todo.sh github` to sync open issues to your `todo.txt` file.
7. When you have closed an issue on github, run the command again to mark them as done. (**Note:** No issue will be closed by marking the todo item as done, instead the issue might be added back to the todo list if it was already archived to the `done.txt` file.)
