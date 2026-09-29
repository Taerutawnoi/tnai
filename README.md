TNAI - Thai News Letter AI

#Code ใน Repo นี้มีการใช้ AI

วิดีโอวิธีการใช้แบบคล่าว ๆ
https://youtu.be/szVckvcrrVc

สิ่งที่ต้องเตรียมในการทำโปรเจค
1. Docker (พร้อม Compose)
2. คอมพิวเตอร์ที่มีแรมอย่างน้อย 12 GB ขึ้นไป

วิธีการทำ
1. Clone หรือดาวน์โหลด Repo นี้ไปไว้ในที่ที่ต้องการเก็บข้อมูลโปรเจค
ตัวอย่าง 
C:/TNAI
/home/TNAI
2. เปิด Terminal ในโฟลเดอร์ที่โคลน Repo
3. docker compose -f docker-compose.yaml up -d
4. เปิดเบราเซอร์แล้วเข้าไปที่
http://localhost:85/setup-wizard.php
หากระบบอยู่ในเครื่องอื่นให้ใส่ IP ของเครื่องนั้นแทน localhost
5. รอจน SetUp เสร็จและใช้งานได้เลย

การเริ่มใช้งาน
1. เริ่มต้นระบบผ่าน CMD หรือ Terminal ด้วยคำสั่งนี้ (หากปิดใช้งานระบบไว้)
docker start tnai_apache tnai_mariadb tnai_ollama tnai_phpmyadmin
2. เปิดเบราเซอร์แล้วเข้าไปที่ http://localhost:85/ หรือ IP ของเครื่องเซิฟเวอร์ (กรณีที่ติดตั้งระบบไว้ในเครื่องอื่น)
3. เริ่มใช้งานได้เลย
