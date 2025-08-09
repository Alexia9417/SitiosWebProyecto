lucide.createIcons();

function updateClock() {
  const now = new Date();
  document.getElementById("current-time").textContent = now.toLocaleTimeString(
    "es-CR",
    {
      hour12: false,
    }
  );
  document.getElementById("current-date").textContent = now.toLocaleDateString(
    "es-CR",
    {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    }
  );
}
setInterval(updateClock, 1000);
updateClock();

document.querySelectorAll(".nav-item").forEach((item) => {
  item.addEventListener("click", () => {
    document
      .querySelectorAll(".nav-item")
      .forEach((i) =>
        i.classList.remove("bg-gray-100", "text-gray-900", "font-semibold")
      );
    item.classList.add("bg-gray-100", "text-gray-900", "font-semibold");
  });
});
updateClock();
