Public Website.

# Update instructions
```
#Back up existing home
tar -czf /home/private/public_$(date +%Y-%m-%d).tar.gz /home/public/


cd /home/tmp
git clone https://github.com/rma92/rm.vg.git
cd /home/tmp/rm.vg
cp /home/public/data/counter.db /home/tmp/rm.vg/data/counter.db
cp -rf /home/tmp/rm.vg/. /home/public
rm -rf /home/tmp/rm.vg
```
