import random
from faker import Faker

fake = Faker()

# Role to ID mapping
roles = {
    "Finance": 1,
    "Doctor": 2,
    "Nurse": 3,
    "Lawyer": 4,
    "Tech": 5,
    "Researcher": 6,
    "Communications": 7,
    "PR": 8
}

# Role to industry mapping
role_to_industry = {
    "Finance": "Finance",
    "Doctor": "Healthcare",
    "Nurse": "Healthcare",
    "Lawyer": "Legal",
    "Tech": "Technology",
    "Researcher": "Education",
    "Communications": "Media",
    "PR": "Public Relations"
}

# Skills by role
skills_by_role = {
    "Finance": "Accounting, Financial Analysis, Excel",
    "Doctor": "Diagnosis, Patient Care, Surgery",
    "Nurse": "Patient Care, Medication Administration, Emergency Response",
    "Lawyer": "Legal Research, Litigation, Client Representation",
    "Tech": "Python, JavaScript, SQL, Web Development",
    "Researcher": "Data Analysis, Report Writing, Research Design",
    "Communications": "Writing, Public Speaking, Content Creation",
    "PR": "Brand Management, Media Relations, Event Planning"
}

# Experience levels
experience_levels = {
    "Entry": (0, 2),
    "Mid": (3, 7),
    "Senior": (8, 14),
    "Executive": (15, 25)
}

# Static file paths
cv_path = "uploads/1751466512_Phoebe Mokoit_CV.docx"
passport_path = "uploads/1751529463_passport_Screenshot 2025-06-24 085806.png"
documents_path = "uploads/documents/sample_doc1.pdf,uploads/documents/sample_doc2.pdf"

# Precomputed bcrypt hash for password "123"
hashed_password = "$2b$12$9r2bIJCyQ7Z5elOTNeAWieXZaYoXhN/7jlR3swtt5RngMTwFMqzKq"

sql_template = """
INSERT INTO users (
    name, email, password, cv, role_id, passport, documents,
    headline, skills, experience, education,
    industry, experience_years, experience_level
) VALUES
"""

users = []

for _ in range(30):
    name = fake.name().replace("'", "''")
    email = fake.email()
    role = random.choice(list(roles.keys()))
    role_id = roles[role]
    industry = role_to_industry[role]
    skills = skills_by_role[role]
    experience_level = random.choice(list(experience_levels.keys()))
    experience_years = random.randint(*experience_levels[experience_level])

    headline = f"Experienced {role} professional"
    experience = f"Worked as a {role} for several years. Skilled in {skills.lower()}.".replace("'", "''")
    education = f"Holds a degree relevant to the {industry} industry.".replace("'", "''")

    users.append(f"('{name}', '{email}', '{hashed_password}', '{cv_path}', {role_id}, "
                 f"'{passport_path}', '{documents_path}', '{headline}', '{skills}', "
                 f"'{experience}', '{education}', '{industry}', {experience_years}, '{experience_level}')")

sql_query = sql_template + ",\n".join(users) + ";"
print(sql_query)
