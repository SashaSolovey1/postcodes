<template>
  <div class="list">
    <h1>Поштові індекси</h1>

    <div class="filters">
      <input v-model="filters.postal_code" placeholder="Фільтр по індексу" @input="updateFilters" />
      <input v-model="filters.settlement" placeholder="Фільтр по населеному пункту" @input="updateFilters" />
    </div>

    <div class="limit-selector">
      <label for="limit">Кількість записів:</label>
      <select id="limit" v-model="limit" @change="updateLimit">
        <option value="50">50</option>
        <option value="20">20</option>
        <option value="10">10</option>
      </select>
    </div>

    <table v-if="postIndexes.length">
      <thead>
      <tr>
        <th>Область</th>
        <th>Район (старий)</th>
        <th>Район (новий)</th>
        <th>Населений пункт</th>
        <th>Поштовий індекс</th>
        <th>Region</th>
        <th>District new</th>
        <th>Settlement</th>
        <th>Вiддiлення</th>
        <th>Post office</th>
        <th>Індекс відділення</th>
        <th>Дії</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="index in postIndexes" :key="index.postal_code">
        <td :title="index.oblast">{{ index.oblast }}</td>
        <td :title="index.old_district">{{ index.old_district }}</td>
        <td :title="index.district_new">{{ index.district_new }}</td>
        <td :title="index.settlement">{{ index.settlement }}</td>
        <td :title="index.postal_code">{{ index.postal_code }}</td>
        <td :title="index.region">{{ index.region }}</td>
        <td :title="index.district_new">{{ index.district_new }}</td>
        <td :title="index.settlement_eng">{{ index.settlement_eng }}</td>
        <td :title="index.post_branch">{{ index.post_branch }}</td>
        <td :title="index.post_office">{{ index.post_office }}</td>
        <td :title="index.post_code_office">{{ index.post_code_office }}</td>
        <td>
          <button @click="deleteIndex(index.postal_code)">Видалити</button>
        </td>
      </tr>
      </tbody>

    </table>
    <p v-else>Немає даних</p>

    <div class="pagination" v-if="totalItems > 0">
      <button v-if="page > 1" @click="prevPage">Попередня</button>
      <span>Сторінка {{ page }} з {{ totalPages }}</span>
      <button v-if="hasNextPage" @click="nextPage">Наступна</button>
    </div>

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
      totalItems: 0,
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
.list{
  width: max-content;
}
table {
  width: fit-content;
  table-layout: fixed;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 160px;
  min-width: 160px;
}

td{
  white-space: nowrap;
}

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
