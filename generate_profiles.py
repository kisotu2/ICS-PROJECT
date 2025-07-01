import random
from faker import Faker

fake = Faker()

roles = ["Finance", "Doctor", "Nurse", "Lawyer", "Tech", "Researcher", "Communications", "PR"]
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

sql_template = """
INSERT INTO users (name, email, password, cv, role_id, passport, documents, headline, skills, experience, education)
VALUES
"""

users = []
for _ in range(30):
    name = fake.name().replace("'", "''")
    email = fake.email()
    password = "hashed_password_123"
    cv = "uploads/cv/sample_cv.pdf"
    passport = "uploads/passport/avatar.png"
    documents = "uploads/documents/sample_doc1.pdf,uploads/documents/sample_doc2.pdf"
    role = random.choice(roles)
    role_id = 1
    headline = f"Experienced {role} professional"
    skills = skills_by_role[role]
    experience = fake.paragraph(nb_sentences=3).replace("'", "''")
    education = fake.paragraph(nb_sentences=2).replace("'", "''")

    users.append(f"('{name}', '{email}', '{password}', '{cv}', {role_id}, '{passport}', '{documents}', "
                 f"'{headline}', '{skills}', '{experience}', '{education}')")

sql_query = sql_template + ",\n".join(users) + ";"
print(sql_query)