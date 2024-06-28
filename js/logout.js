function logoutConfirm() {
  const logoutButton = document.getElementById("logoutButton");
  const confirmText = confirm("ログアウトしますか？");
  if (!confirmText) {
    logoutButton.setAttribute("href", "");
  }
}

function confirmWithDelete() {
  if (confirm("退会してもよろしいでしょうか？")) {
    document.getElementById("delete").submit();
  }
}

