<template>
  <div>
    <h1>Поштові індекси</h1>

    <!-- Фільтр -->
    <div class="filters">
      <input v-model="filters.postal_code" placeholder="Фільтр по індексу" @input="updateFilters" />
      <input v-model="filters.settlement" placeholder="Фільтр по населеному пункту" @input="updateFilters" />
    </div>

    <!-- Вибір ліміту записів -->
    <div class="limit-selector">
      <label for="limit">Кількість записів:</label>
      <select id="limit" v-model="limit" @change="updateLimit">
        <option value="50">50</option>
        <option value="20">20</option>
        <option value="10">10</option>
      </select>
    </div>

    <!-- Таблиця з даними -->
    <table v-if="postIndexes.length">
      <thead>
      <tr>
        <th>Область</th>
        <th>Район (старий)</th>
        <th>Район (новий)</th>
        <th>Населений пункт</th>
        <th>Поштовий індекс (Postal code)</th>
        <th>Region (Oblast)</th>
        <th>District new (Raion new)</th>
        <th>Settlement</th>
        <th>Вiддiлення зв`язку</th>
        <th>Post office</th>
        <th>Поштовий індекс відділення зв`язку (Post code of post office)</th>
        <th>Дії</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="index in postIndexes" :key="index.postal_code">
        <td>{{ index.oblast }}</td>
        <td>{{ index.old_district }}</td>
        <td>{{ index.district_new }}</td>
        <td>{{ index.settlement }}</td>
        <td>{{ index.postal_code }}</td>
        <td>{{ index.region }}</td>
        <td>{{ index.district_new }}</td>
        <td>{{ index.settlement_eng }}</td>
        <td>{{ index.post_branch }}</td>
        <td>{{ index.post_office }}</td>
        <td>{{ index.post_code_office }}</td>
        <td>
          <button @click="deleteIndex(index.postal_code)">Видалити</button>
        </td>
      </tr>
      </tbody>
    </table>
    <p v-else>Немає даних</p>

    <!-- Пагінація -->
    <div class="pagination" v-if="totalItems > 0">
      <button v-if="page > 1" @click="prevPage">Попередня</button>
      <span>Сторінка {{ page }} з {{ totalPages }}</span>
      <button v-if="hasNextPage" @click="nextPage">Наступна</button>
    </div>

    <!-- Форма додавання запису -->
    <AddPostIndex @index-added="fetchPostIndexes" />
  </div>
</template>

<script>
import api from "@/api";
import AddPostIndex from "@/components/AddPostIndex.vue";

export default {
  components: { AddPostIndex },
  data() {
    return {
      postIndexes: [],
      filters: {
        postal_code: "",
        settlement: "",
      },
      page: 1,
      limit: 50,
      totalItems: 0, // Загальна кількість записів
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.totalItems / this.limit);
    },
    hasNextPage() {
      return this.page < this.totalPages;
    },
  },
  async created() {
    this.fetchPostIndexes();
  },
  methods: {
    async fetchPostIndexes() {
      const params = { ...this.filters, page: this.page, limit: this.limit };

      const response = await api.get("/post-indexes", { params });
      this.postIndexes = response.data.items;
      this.totalItems = response.data.total;
    },
    async deleteIndex(postal_code) {
      await api.delete(`/post-indexes/${postal_code}`);
      this.fetchPostIndexes();
    },
    prevPage() {
      if (this.page > 1) {
        this.page--;
        this.fetchPostIndexes();
      }
    },
    nextPage() {
      if (this.hasNextPage) {
        this.page++;
        this.fetchPostIndexes();
      }
    },
    updateFilters() {
      this.page = 1;
      this.fetchPostIndexes();
    },
    updateLimit() {
      this.page = 1;
      this.fetchPostIndexes();
    },
  },
};
</script>

<style scoped>
.filters input {
  margin-right: 10px;
  padding: 5px;
}

.limit-selector {
  margin: 10px 0;
}

.limit-selector label {
  margin-right: 10px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

button {
  background-color: red;
  color: white;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
}

.pagination {
  margin-top: 20px;
}

.pagination button {
  margin-right: 10px;
  margin-left: 10px;
  border-radius: 5px;
  padding: 5px;
}
</style>
