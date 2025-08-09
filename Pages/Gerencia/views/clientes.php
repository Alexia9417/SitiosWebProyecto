<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel Ejecutivo</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-[#000] text-white font-sans leading-relaxed flex min-h-screen">
  <div class="page-wrapper flex w-full">
    <!-- Sidebar -->
    <?php include 'Template/nav.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 max-w-[calc(100%-250px)] mx-auto p-6 overflow-y-auto">
      <header class="flex flex-col md:flex-row md:justify-between md:items-center pb-5 border-b border-gray-200 mb-5">
        <h1 class="text-3xl font-bold mb-4 md:mb-0">Panel de Clientes</h1>
        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
          <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow">
            <i data-lucide="calendar" class="w-5 h-5"></i>
            <span id="current-date">--</span>
          </div>
          <div class="flex items-center gap-2 bg-green-50 text-green-600 font-semibold px-4 py-2 rounded-lg shadow">
            <i data-lucide="clock" class="w-5 h-5"></i>
            <span id="current-time">--:--:--</span>
          </div>
        </div>
      </header>

      <!-- Fondo general -->
      <div class="min-h-screen bg-[#F5DDD3] text-[#000] p-6">

        <!-- Dashboard Cards -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Total Reseñas -->
          <div class="bg-[#ffffff] rounded-xl shadow p-4 flex justify-between items-center border border-[#E9C89A]">
            <div>
              <h3 class="text-sm text-[#4E5D83]">Total Reseñas</h3>
              <p id="totalResenas" class="text-2xl font-bold text-[#4E5D83]">0</p>
            </div>
            <i class="fa-regular fa-comment text-3xl text-[#967ED5]"></i>
          </div>

          <!-- Reseñas Positivas -->
          <div class="bg-[#ffffff] rounded-xl shadow p-4 flex justify-between items-center border border-[#E9C89A]">
            <div>
              <h3 class="text-sm text-[#4E5D83]">Reseñas Positivas</h3>
              <p id="porcentajePositivas" class="text-2xl font-bold text-[#4E5D83]">0%</p>
              <p class="text-xs text-[#967ED5]">4-5 estrellas</p>
            </div>
            <i class="fa-regular fa-thumbs-up text-3xl text-[#4CAF50]"></i>
          </div>

          <!-- Reseñas Negativas -->
          <div class="bg-[#ffffff] rounded-xl shadow p-4 flex justify-between items-center border border-[#E9C89A]">
            <div>
              <h3 class="text-sm text-[#4E5D83]">Reseñas Negativas</h3>
              <p id="porcentajeNegativas" class="text-2xl font-bold text-[#4E5D83]">0%</p>
              <p class="text-xs text-[#967ED5]">1-2 estrellas</p>
            </div>
            <i class="fa-regular fa-thumbs-down text-3xl text-[#f87171]"></i>
          </div>
        </section>


        <!-- Reseñas -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Lista de Reseñas -->
          <div class="col-span-2 bg-[#ffffff] rounded-xl shadow p-6 border border-[#BFBCE9]">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-2 text-lg font-semibold text-[#4E5D83]">
                <i class="fa-regular fa-comment"></i>
                <span>Reseñas de Clientes</span>
              </div>
              <span class="text-sm bg-[#BFBCE9] text-[#4E5D83] px-2 py-1 rounded-full">8</span>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-4" id="filtroResenas">
              <button data-filtro="todas" class="filtro-btn px-3 py-1 rounded bg-[#4E5D83] text-white text-sm">Todas</button>
              <button data-filtro="positivas" class="filtro-btn px-3 py-1 rounded bg-[#BFBCE9] text-[#4E5D83] text-sm">Positivas</button>
              <button data-filtro="neutras" class="filtro-btn px-3 py-1 rounded bg-[#BFBCE9] text-[#4E5D83] text-sm">Neutras</button>
              <button data-filtro="negativas" class="filtro-btn px-3 py-1 rounded bg-[#BFBCE9] text-[#4E5D83] text-sm">Negativas</button>
            </div>

            <!-- Lista de ítems -->
            <div id="contenedorClientes" class="space-y-4"></div>

          </div>

          <!-- Gráfico de calificaciones -->
          <div class="bg-[#ffffff] rounded-xl shadow p-6 border border-[#BFBCE9]">
            <div class="flex items-center gap-2 text-lg font-semibold text-[#4E5D83] mb-4">
              <i class="fa-regular fa-star"></i>
              <h2>Distribución de Calificaciones</h2>
            </div>
            <div class="flex justify-between items-end h-48 gap-2">
              <div class="flex flex-col items-center">
                <div class="w-8 bg-[#BFBCE9] h-12 flex items-center justify-center text-xs text-[#000]">1</div>
                <span class="text-sm mt-1 text-[#4E5D83]">1<i class="fa-solid fa-star ml-1"></i></span>
              </div>
              <div class="flex flex-col items-center">
                <div class="w-8 bg-[#BFBCE9] h-12 flex items-center justify-center text-xs text-[#000]">1</div>
                <span class="text-sm mt-1 text-[#4E5D83]">2<i class="fa-solid fa-star ml-1"></i></span>
              </div>
              <div class="flex flex-col items-center">
                <div class="w-8 bg-[#BFBCE9] h-12 flex items-center justify-center text-xs text-[#000]">1</div>
                <span class="text-sm mt-1 text-[#4E5D83]">3<i class="fa-solid fa-star ml-1"></i></span>
              </div>
              <div class="flex flex-col items-center">
                <div class="w-8 bg-[#E9C89A] h-24 flex items-center justify-center text-xs text-[#000]">2</div>
                <span class="text-sm mt-1 text-[#4E5D83]">4<i class="fa-solid fa-star ml-1"></i></span>
              </div>
              <div class="flex flex-col items-center">
                <div class="w-8 bg-[#967ED5] h-32 flex items-center justify-center text-xs text-white">3</div>
                <span class="text-sm mt-1 text-[#4E5D83]">5<i class="fa-solid fa-star ml-1"></i></span>
              </div>
            </div>
            <p class="text-center text-sm text-[#4E5D83] mt-4">Total de 8 reseñas</p>
          </div>
        </section>
      </div>
    </div>
  </div>
  <script src="/Sitios/Gerente/Public/JS/Gerente/clientes.js"></script>
  <script src="/Sitios/Gerente/Public/JS/Gerente/time.js"></script>
</body>

</html>