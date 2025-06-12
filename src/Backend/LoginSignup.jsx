import React, { useState } from "react";
import "../App.css";
import axios from "axios";

function LoginSignup() {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    password: "",
  });

  const handleChange = (e) => {
    setFormData({...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const res = await axios.post("http://localhost:8000/server.php", formData);
      alert(res.data.message);
    } catch (error) {
      console.error("Error sending data:", error);
    }
  };

  return (
    <div className="App">
      <h2>Sign Up / Login</h2>
      <form onSubmit={handleSubmit}>
        <input
          name="name"
          type="text"
          placeholder="Name"
          onChange={handleChange}
        />
        <input
          name="email"
          type="email"
          placeholder="Email"
          onChange={handleChange}
        />
        <input
          name="password"
          type="password"
          placeholder="Password"
          onChange={handleChange}
        />
        <button type="submit">Submit</button>
      </form>
    </div>
  );
}

export default LoginSignup;
