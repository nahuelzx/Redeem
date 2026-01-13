---

# 🎁 Redeem

Redeem is a simple plugin designed for HCF servers that allows you to give **rewards using codes**.
The idea is to let you create special drops or event rewards without touching code or restarting the server.

---

## ✨ What does this plugin do?

* Lets you create **redeem codes** with any rewards you want.
* You can edit the items of each redeem using a **/redeem edit**.
* Changes are saved **when closing the inventory**, no server restart needed.
* Each player can **claim only one redeem** in total (simple anti-abuse system).

---

## 🚀 How to install

1. Download the `.phar` or the plugin source code.
2. Place it in the `plugins/` folder of your **PocketMine-MP 5** server.
3. Start or reload the server.
4. Configure your codes and rewards in `redeems.yml`.

That’s it — you can now use `/redeem` on the server.

---

## 🧾 Important files

### `redeems.yml`

This is where **codes** and **rewards** for each redeem are stored.
It’s the file you need to edit to change kits, items, and codes.

### `claims.yml`

This file stores which players have already claimed a redeem.
You don’t need to edit it manually; the plugin handles it automatically.

> Editing data files manually is not recommended unless you know exactly what you’re doing.

---

## 🗨 Commands

| Command                 | Description                    |
| ----------------------- | ------------------------------ |
| `/redeem claim <name>`  | Claim the rewards of a redeem. |
| `/redeem create <name>` | Create a new redeem.           |
| `/redeem edit <name>`   | Edit an existing redeem.       |

Permissions:

* `redeem.command` → use `/redeem`.
* `redeem.create` → create new redeems.
* `redeem.edit` → edit redeems.

---

## 📜 License

This project uses the **MIT** license.
You are free to use, modify, and share it, as long as the original license is kept.

---

## 📬 Contact

If you find a bug or have new ideas:

* Discord: **635u**

Thanks for using **Redeem** ❤️
