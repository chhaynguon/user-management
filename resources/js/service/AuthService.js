import api from "./api";

export default {
  login(email, password) {
    return api.post("/auth/login", { email, password });
  },
  register(payload) {
    return api.post("/auth/register", payload);
  },
  logout() {
    return api.post("/auth/logout");
  },
  me(token) {
    return fetch(
      `${import.meta.env.VITE_API_URL || "http://localhost:8000"
      }/api/auth/me`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
        },
      }
    ).then((res) => res.json());
  },
};
