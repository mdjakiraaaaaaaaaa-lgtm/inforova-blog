# INFOROVA-Updated1 — One-command Termux setup

এই প্যাকেজে `run-termux.sh` আছে। লক্ষ্য হলো আলাদা করে DB_HOST/DB_NAME/USER/PASSWORD হাতে না বসিয়ে যতটা সম্ভব স্বয়ংক্রিয়ভাবে সেটআপ করা।

## চালানোর নিয়ম

```bash
cd ~/storage/downloads
unzip INFOROVA-Updated1.zip
cd INFOROVA-Updated1
bash run-termux.sh
```

Browser:
- INFOROVA-Updated1: http://127.0.0.1:8001

স্ক্রিপ্টটি:
1. PHP + MariaDB ইনস্টল করার চেষ্টা করবে
2. MariaDB চালু করবে
3. `shared_db` database ও local user তৈরি করবে
4. project-এর `.sql` ফাইল থাকলে import করবে
5. প্রচলিত PHP/.env DB settings স্বয়ংক্রিয়ভাবে `shared_db`-এ সেট করবে
6. PHP localhost server চালু করবে

## গুরুত্বপূর্ণ
দুই application-এর database schema যদি পরস্পর incompatible হয়, শুধু একই database নাম দিলেই integration সম্পূর্ণ হয় না। সেই ক্ষেত্রে রান করার সময় যে error দেখাবে সেটাই পরের সংশোধনের ভিত্তি হবে।
