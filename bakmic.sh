export po=mic
for suf in binomes logins exp1 exp2 exp3 exp4 exp5
do
echo boo_${po}_${suf}
mysqldump -hsourcecojln.mysql.db -usourcecojln -pJWhxQ1 sourcecojln boo_${po}_${suf} > ${po}_${suf}.sql
done
