import React, { useState } from "react";
import '../Frontend/LoginSignup.css';

import user_icon from '../Assets/user.png';
import email_icon from '../Assets/email.png';
import password_icon from '../Assets/password.png';

const LoginSignup = () => {
    const [action, setAction] = useState("Sign Up");
    const [role, setRole] = useState("job_seeker");
    const [formData, setFormData] = useState({
        name: "",
        email: "",
        password: "",
    });

    const handleInputChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (actionType) => {
        const endpoint = actionType === "Sign Up" ? "signup.php" : "login.php";

        const response = await fetch(`http://localhost/job_marketplace/backend/${endpoint}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ ...formData, role })
        });

        const data = await response.json();
        alert(data.message);

        // Redirect after success
        if (data.success) {
            if (data.role === "job_seeker") {
                window.location.href = "/job_seeker_page";
            } else if (data.role === "organization") {
                window.location.href = "/organization_page";
            } else if (data.role === "admin") {
                window.location.href = "/admin_page";
            }
        }
    };

    return (
        <div className="container">
            <div className="header">
                <div className="text">{action}</div>
                <div className="underline"></div>
            </div>

            <div className="Inputs">
                {action === "Login" ? null : (
                    <div className="input">
                        <img src={user_icon} alt="User Icon" />
                        <input
                            type="text"
                            placeholder="Full Name"
                            name="name"
                            value={formData.name}
                            onChange={handleInputChange}
                        />
                    </div>
                )}

                <div className="input">
                    <img src={email_icon} alt="Email Icon" />
                    <input
                        type="email"
                        placeholder="Email Address"
                        name="email"
                        value={formData.email}
                        onChange={handleInputChange}
                    />
                </div>

                <div className="input">
                    <img src={password_icon} alt="Password Icon" />
                    <input
                        type="password"
                        placeholder="Password"
                        name="password"
                        value={formData.password}
                        onChange={handleInputChange}
                    />
                </div>

                <select value={role} onChange={(e) => setRole(e.target.value)} className="role-select">
                    <option value="job_seeker">Job Seeker</option>
                    <option value="organization">Organization</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            {action === "Login" && (
                <div className="forgot-password">
                    Forgot Password? <span>Click Here</span>
                </div>
            )}

            <div className="submit-container">
                <div
                    className={action === "Sign Up" ? "submit gray" : "submit"}
                    onClick={() => {
                        setAction("Sign Up");
                        handleSubmit("Sign Up");
                    }}
                >
                    Sign Up
                </div>
                <div
                    className={action === "Login" ? "submit gray" : "submit"}
                    onClick={() => {
                        setAction("Login");
                        handleSubmit("Login");
                    }}
                >
                    Login
                </div>
            </div>
        </div>
    );
};

export default LoginSignup;
