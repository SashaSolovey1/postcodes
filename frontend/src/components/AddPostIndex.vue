<template>
  <form @submit.prevent="addIndex">
    <div class="form-group">
      <input v-model="newIndex.oblast" placeholder="Область" required />
      <input v-model="newIndex.region" placeholder="Регіон" required />
    </div>

    <div class="form-group">
      <input v-model="newIndex.old_district" placeholder="Старий район" required />
      <input v-model="newIndex.new_district" placeholder="Новий район" required />
    </div>

    <div class="form-group">
      <input v-model="newIndex.settlement" placeholder="Населений пункт" required />
      <input v-model="newIndex.settlement_eng" placeholder="Населений пункт (англ.)" required />
    </div>

    <div class="form-group">
      <input v-model="newIndex.postal_code" placeholder="Поштовий код" required pattern="\d{5}" title="Повинно містити 5 цифр" />
      <input v-model="newIndex.post_code_office" placeholder="Поштовий код відділення" required pattern="\d{5}" title="Повинно містити 5 цифр" />
    </div>

    <div class="form-group">
      <input v-model="newIndex.post_branch" placeholder="Відділення зв’язку" required />
      <input v-model="newIndex.post_office" placeholder="Поштове відділення" required />
    </div>

    <button type="submit">Додати</button>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </form>
</template>

<script>
import api from "@/api";

export default {
  data() {
    return {
      newIndex: {
        oblast: "",
        old_district: "",
        new_district: "",
        settlement: "",
        postal_code: "",
        region: "",
        district_new: "",
        settlement_eng: "",
        post_branch: "",
        post_office: "",
        post_code_office: "",
      },
      errorMessage: "",
    };
  },
  methods: {
    async addIndex() {
      try {
        await api.post("/post-indexes", this.newIndex);
        this.$emit("index-added");
        this.resetForm();
      } catch (error) {
        this.errorMessage = error.response?.data?.error || "Помилка додавання";
      }
    },
    resetForm() {
      this.newIndex = {
        oblast: "",
        old_district: "",
        new_district: "",
        settlement: "",
        postal_code: "",
        region: "",
        district_new: "",
        settlement_eng: "",
        post_branch: "",
        post_office: "",
        post_code_office: "",
      };
      this.errorMessage = "";
    },
  },
};
</script>

<style scoped>
form {
  margin-top: 50px;
  margin-bottom: 50px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

.form-group {
  display: flex;
  gap: 10px;
}

input {
  flex: 1;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button {
  background-color: green;
  color: white;
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.error {
  color: red;
  margin-top: 10px;
}
</style>
